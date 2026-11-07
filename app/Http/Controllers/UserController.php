<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Mail\AccountCreated;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        return view('users.index', compact('users'));
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
        $generatedPassword = Str::password(20);
        $user = new User([
            ...$request->validated(),
            'password' => Hash::make($generatedPassword),
            'is_active' => true,
            'is_verified' => true,
        ]);
        $user->email_verified_at = now();
        $user->save();

        try {
            Mail::to($user->email)->send(new AccountCreated(
                $user->name, $user->email, $generatedPassword, $user->role,
            ));
        } catch (TransportExceptionInterface $exception) {
            return redirect()->route('users.index')->with('error',
                'Account created, but the login details email could not be sent. The user can use Forgot Password to set a password.');
        }

        return redirect()->route('users.index')
            ->with('success', 'Account created. Login details have been emailed to '.$user->email.'.');
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

        if (DB::table('work_requests')->where('user_id', $user->id)
            ->orWhere('requester_id', $user->id)->orWhere('assigned_to', $user->id)->exists()
            || DB::table('facility_requests')->where('user_id', $user->id)->exists()
            || DB::table('inventory_entries')->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'This account has linked requests or inventory records and cannot be removed.');
        }

        try {
            DB::transaction(function () use ($user): void {
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
