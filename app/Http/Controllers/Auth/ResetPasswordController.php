<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function showRequiredResetForm(Request $request): View|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => Password::broker()->createToken($request->user()),
            'email' => $request->user()->email,
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.regex' => 'Only @psu.edu.ph email addresses are allowed.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                if ($user->must_change_password && Hash::check($password, $user->password)) {
                    throw ValidationException::withMessages([
                        'password' => 'Choose a new password different from your current password.',
                    ]);
                }
                $user->forceFill([
                    'password' => Hash::make($password),
                    'is_active' => $user->must_change_password || ! $user->is_verified || $user->is_active,
                    'must_change_password' => false,
                    'is_verified' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
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
