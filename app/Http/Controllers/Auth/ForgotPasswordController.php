<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    /**
     * Show the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@psu\.edu\.ph$/',
            ],
        ], [
            'email.regex' => '❌ Only @psu.edu.ph email addresses are allowed.',
            'email.required' => '❌ Email address is required.',
            'email.email' => '❌ Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', '❌ No account found with this email address.');
        }

        // Generate reset token
        $token = Str::random(64);

        // Delete old tokens
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Store token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Send custom email
        $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);

        Mail::send('emails.password-reset', [
            'name' => $user->name,
            'email' => $user->email,
            'resetLink' => $resetLink,
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Reset Your Password - PPMMIS')
                ->from(config('mail.from.address'), config('mail.from.name'));
        });

        return back()->with('success', '✅ We have emailed your password reset link! Please check your inbox.');
    }
}
