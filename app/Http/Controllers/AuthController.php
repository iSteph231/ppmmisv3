<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Show Login Page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Login Function
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@psu\.edu\.ph$/'
            ],
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

                if (Auth::user()->role === 'admin') {
                return redirect('/admin/dashboard');
            }

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    // Show Register Page
    public function showRegister()
    {
        return view('auth.register');
    }

    // Register Function
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@psu\.edu\.ph$/'
            ],
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // Logout Function
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}