<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $users = $query->latest()->paginate(15);
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user,personnel',
            'department' => 'nullable|string',
            'phone_number' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'phone_number' => $request->phone_number,
            'is_active' => true,
        ]);
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
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
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,user,personnel',
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
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
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
                'message' => 'You cannot change your own status.'
            ], 403);
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully!',
            'is_active' => $user->is_active
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
            'message' => 'Password reset successfully!'
        ]);
    }
    
    /**
     * Search users (AJAX)
     */
    public function search(Request $request)
    {
        $query = User::query();
        
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%');
        }
        
        $users = $query->limit(10)->get();
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}