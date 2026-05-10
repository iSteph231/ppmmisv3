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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
}