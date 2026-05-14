<?php

namespace App\Models;

// Correct namespace for Authenticatable
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // admin, user, personnel
        'is_active',
        'department',
        'phone_number',
        'is_verified',   // Email verification status
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',           // Hide OTP from serialization
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Get work requests for this user
     */
    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user (requester)
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if user is personnel (handles maintenance work)
     */
    public function isPersonnel()
    {
        return $this->role === 'personnel';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail()
    {
        return $this->is_verified === true;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified()
    {
        return $this->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Generate and set OTP for user
     */
    public function generateOTP()
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);
        
        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($otp)
    {
        return $this->otp === $otp && 
               $this->otp_expires_at && 
               $this->otp_expires_at->isFuture();
    }

    /**
     * Clear OTP after verification or expiry
     */
    public function clearOTP()
    {
        return $this->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Check if OTP is expired
     */
    public function isOTPExpired()
    {
        return $this->otp_expires_at && $this->otp_expires_at->isPast();
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for unverified users
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope for personnel
     */
    public function scopePersonnel($query)
    {
        return $query->where('role', 'personnel');
    }

    /**
     * Scope for regular users (requesters)
     */
    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Get full name with department
     */
    public function getFullNameWithDepartmentAttribute()
    {
        return $this->department ? "{$this->name} ({$this->department})" : $this->name;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-clear expired OTPs when retrieving user
        static::retrieved(function ($user) {
            if ($user->otp && $user->isOTPExpired()) {
                $user->clearOTP();
            }
        });
    }
}