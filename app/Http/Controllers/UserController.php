<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveAccountRequestRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Mail\AccountCreated;
use App\Models\AccountRequest;
use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $accountRequests = AccountRequest::oldest()->paginate(10, ['*'], 'requests_page');

        return view('users.index', compact('users', 'accountRequests'));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->is($request->user()) && $request->validated('role') !== 'admin') {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own administrator role.',
            ]);
        }

        $user->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role saved.', 'role' => $user->role]);
        }

        return back()->with('success', 'Role updated for '.$user->name.'.');
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->createPendingAccount($request->validated());
        AccountRequest::where('email', $user->email)->delete();

        return $this->sendAccountSetupLink($user);
    }

    /** @param array{name: string, email: string, role: string, department?: ?string} $data */
    private function createPendingAccount(array $data): User
    {
        $user = new User([
            ...$data,
            'password' => Hash::make(Str::random(64)),
            'is_active' => false,
            'is_verified' => false,
        ]);
        $user->must_change_password = true;
        $user->save();

        return $user;
    }

    public function approveAccountRequest(ApproveAccountRequestRequest $request, AccountRequest $accountRequest): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $accountRequest): User {
            $pendingRequest = AccountRequest::lockForUpdate()->findOrFail($accountRequest->id);
            if (User::where('email', $pendingRequest->email)->exists()) {
                throw ValidationException::withMessages(['email' => 'An account already exists for this email address.']);
            }
            $user = $this->createPendingAccount([
                ...$pendingRequest->only(['name', 'email', 'department']),
                'role' => $request->validated('role'),
            ]);
            $pendingRequest->delete();

            return $user;
        });

        return $this->sendAccountSetupLink($user);
    }

    public function declineAccountRequest(AccountRequest $accountRequest): RedirectResponse
    {
        $accountRequest->delete();

        return redirect()->route('users.index')->with('success', 'Account request declined.');
    }

    private function sendAccountSetupLink(User $user): RedirectResponse
    {
        $changePasswordUrl = route('password.reset', [
            'token' => Password::broker()->createToken($user), 'email' => $user->email,
        ]);

        try {
            Mail::to($user->email)->send(new AccountCreated(
                $user->name, $user->email, $user->role, $changePasswordUrl,
            ));
        } catch (TransportExceptionInterface $exception) {
            return redirect()->route('users.index')->with('error',
                'Account created, but the password setup email could not be sent. The user can use Forgot Password to set a password.');
        }

        return redirect()->route('users.index')
            ->with('success', 'Account created. A password setup link has been emailed to '.$user->email.'.');
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with('workRequests')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:admin,user,engineer',
            'department' => 'nullable|string',
            'phone_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        if (DB::table('facility_requests')->where('user_id', $user->id)->exists()
            || DB::table('inventory_entries')->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'This account has linked facility requests or inventory records and cannot be removed.');
        }

        try {
            DB::transaction(function () use ($user): void {
                WorkRequest::withTrashed()->where(function ($query) use ($user): void {
                    $query->where('user_id', $user->id)
                        ->orWhere(function ($query) use ($user): void {
                            $query->whereNull('user_id')->where('requester_id', $user->id);
                        });
                })->lockForUpdate()->get()->each(function (WorkRequest $workRequest) use ($user): void {
                    $workRequest->requester_snapshot = $user->only(['name', 'email', 'department', 'phone_number']);
                    $workRequest->requester_name = $user->name;
                    $workRequest->user_id = null;
                    if ($workRequest->requester_id === $user->id) {
                        $workRequest->requester_id = null;
                    }
                    $workRequest->save();
                });
                DB::table('work_requests')->where('requester_id', $user->id)->update(['requester_id' => null]);
                DB::table('work_requests')->where('assigned_to', $user->id)->update(['assigned_to' => null]);
                DB::table('sessions')->where('user_id', $user->id)->delete();
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                $user->delete();
            });
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->with('error', 'This account has linked records and cannot be removed.');
        }

        return redirect()->route('users.index')->with('success', 'Account removed successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.',
            ], 403);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully!',
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully!',
        ]);
    }

    /**
     * Search users (AJAX)
     */
    public function search(Request $request)
    {
        $query = User::query();

        if ($request->has('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%');
        }

        $users = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
