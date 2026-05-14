<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Cache\RateLimiter;

class OTPVerificationController extends Controller
{
    protected $rateLimiter;
    
    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
    
    /**
     * Show OTP verification form.
     */
    public function showVerificationForm(Request $request)
    {
        // Check if there's a pending registration
        if (!Session::has('pending_registration')) {
            return redirect()->route('register')
                           ->with('error', 'No pending registration found. Please register first.');
        }
        
        $pendingData = Session::get('pending_registration');
        $email = $pendingData['email'];
        
        return view('auth.verify-otp', compact('email'));
    }
    
    /**
     * Verify OTP and create user account.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);
        
        // Check if there's a pending registration
        if (!Session::has('pending_registration')) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found. Please register again.'
            ], 422);
        }
        
        $pendingData = Session::get('pending_registration');
        
        // Verify OTP
        if ($pendingData['otp'] !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 422);
        }
        
        // Check if OTP is expired
        if (now()->gt($pendingData['otp_expires_at'])) {
            Session::forget('pending_registration');
            
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please register again.'
            ], 422);
        }
        
        // Double-check if email already exists
        if (User::where('email', $pendingData['email'])->exists()) {
            Session::forget('pending_registration');
            
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered. Please login.'
            ], 422);
        }
        
        // Create user account
        $user = User::create([
            'name' => $pendingData['name'],
            'email' => $pendingData['email'],
            'password' => Hash::make($pendingData['password']),
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);
        
        // Clear pending registration
        Session::forget('pending_registration');
        Session::forget('registration_email');
        
        // Log the user in
        Auth::login($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'redirect' => route('dashboard')
        ]);
    }
    
    /**
     * Resend OTP with 30-second rate limiting.
     */
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        // Apply rate limiting - 1 request every 30 seconds
        $key = 'otp-resend:' . $request->email;
        
        if ($this->rateLimiter->tooManyAttempts($key, 1)) {
            $seconds = $this->rateLimiter->availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before requesting a new OTP."
            ], 429);
        }
        
        // Get pending registration data
        if (!Session::has('pending_registration')) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found. Please register again.'
            ], 422);
        }
        
        $pendingData = Session::get('pending_registration');
        
        // Verify email matches
        if ($pendingData['email'] !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Email mismatch. Please register again.'
            ], 422);
        }
        
        // Generate new OTP
        $newOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update session with new OTP
        $pendingData['otp'] = $newOtp;
        $pendingData['otp_expires_at'] = now()->addMinutes(10);
        Session::put('pending_registration', $pendingData);
        
        // Record rate limit attempt
        $this->rateLimiter->hit($key, 30); // Lock for 30 seconds
        
        // Resend OTP email
        Mail::send('emails.otp', [
            'otp' => $newOtp, 
            'email' => $pendingData['email'], 
            'name' => $pendingData['name']
        ], function ($message) use ($pendingData) {
            $message->to($pendingData['email'])
                    ->subject('Email Verification - PPMMIS')
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
        
        return response()->json([
            'success' => true,
            'message' => 'New OTP has been sent to your email.',
            'cooldown' => 30
        ]);
    }
}