<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->with('error', '❌ No account found with this email address.')
                         ->withInput($request->only('email', 'remember'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Check if user is active
            if (Auth::user()->is_active != true) {
                Auth::logout();
                return back()->with('error', '⚠️ Your account is deactivated. Please contact the administrator.')
                             ->withInput($request->only('email', 'remember'));
            }
            
            // Redirect based on role
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/dashboard')->with('success', 'Welcome back, ' . Auth::user()->name . '!');
            }
            
            return redirect()->intended('/dashboard')->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        // Login failed - incorrect password
        return back()->with('error', '❌ Incorrect password. Please try again.')
                     ->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}