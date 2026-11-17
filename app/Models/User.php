<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'department',
        'phone_number',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isEngineer(): bool
    {
        return $this->role === 'engineer';
    }

    public function isAccountActive(): bool
    {
        if ($this->isAdmin()) {
            return $this->is_active;
        }

        return $this->is_active && $this->hasVerifiedEmail() && ! $this->must_change_password;
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasVerifiedEmail()
    {
        return $this->is_verified === true;
    }

    public function markEmailAsVerified()
    {
        return $this->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    public function generateOTP()
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    public function verifyOTP($otp)
    {
        return $this->otp === $otp &&
               $this->otp_expires_at &&
               $this->otp_expires_at->isFuture();
    }

    public function clearOTP()
    {
        return $this->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    public function isOTPExpired()
    {
        return $this->otp_expires_at && $this->otp_expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }

    public function getFullNameWithDepartmentAttribute()
    {
        return $this->department ? "{$this->name} ({$this->department})" : $this->name;
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($user) {
            if ($user->otp && $user->isOTPExpired()) {
                $user->clearOTP();
            }
        });
    }
}
