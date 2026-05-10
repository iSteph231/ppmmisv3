<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $table = 'notifications';
    
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'detail',
        'type',
        'is_read',
        'related_id',
        'related_type',
        'created_at'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });
    }
}