<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Ensure user is not logged in during password reset
        // $this->middleware('guest');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@psu\.edu\.ph$/',
            ],
            'password' => 'required|min:8|confirmed',
        ], [
            'email.regex' => 'Only @psu.edu.ph email addresses are allowed.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', '✅ Your password has been reset! Please login with your new password.');
        }

        // Handle different error responses
        $errorMessages = [
            Password::INVALID_USER => 'No account found with this email address.',
            Password::INVALID_TOKEN => 'Invalid or expired password reset link. Please request a new one.',
            Password::RESET_THROTTLED => 'Please wait before retrying.',
        ];

        $errorMessage = $errorMessages[$response] ?? 'Invalid reset link or expired token. Please request a new password reset.';

        return back()->withErrors(['email' => '❌ '.$errorMessage]);
    }
}
