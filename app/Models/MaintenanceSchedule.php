<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $table = 'maintenance_schedules';

    protected $fillable = [
        'scheduled_date',
        'activity',
        'maintenance_in_charge',
        'engineer_in_charge',
        'remarks',
        'month_year',
        'status',
        'completed_at',
        'completion_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Get notifications for this maintenance schedule
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get formatted scheduled date
     */
    public function getFormattedScheduledDateAttribute()
    {
        return $this->scheduled_date ? $this->scheduled_date->format('F d, Y') : 'N/A';
    }
}