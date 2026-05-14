<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request - Store in session, NOT database.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store registration data in session temporarily (NOT in database)
        Session::put('pending_registration', [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Store plain password temporarily (will be hashed when creating user)
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);
        
        // Send OTP to email
        $this->sendOTP($request->email, $otp, $request->name);
        
        // Store email in session for reference
        session(['registration_email' => $request->email]);
        
        // Redirect to OTP verification page
        return redirect()->route('otp.verification.form')
                        ->with('success', 'Please verify your email address. An OTP has been sent to ' . $request->email);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users', // This checks if email already EXISTS in database (verified users only)
                'regex:/^[a-zA-Z0-9._%+-]+@psu\.edu\.ph$/'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.regex' => 'Only @psu.edu.ph email addresses are allowed.',
            'email.unique' => 'This email address is already registered and verified.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'name.required' => 'Full name is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);
    }

    /**
     * Send OTP to user's email.
     */
    protected function sendOTP($email, $otp, $name)
    {
        Mail::send('emails.otp', [
            'otp' => $otp, 
            'email' => $email, 
            'name' => $name
        ], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Email Verification - PPMMIS')
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }
}