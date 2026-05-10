<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_requests';

    protected $fillable = [
        'user_id',
        'request_number',
        'title',
        'description',
        'department',      // Department is now available
        'building_name',   // Building name is now available
        'office_room',     // Office/room is now available
        'work_type',
        'ocular_details',
        'installation_details',
        'repair_details',
        'replacement_details',
        'others_details',
        'status',
        'admin_notes',
        'completed_at',
        'scheduled_date',    // Date when inspection is scheduled
        'inspection_notes',  // Internal notes for inspection (admin only)
        'priority', // Keep for backward compatibility, but not used in form
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'scheduled_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user that owns the work request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the requester (alias for user)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get formatted work type with details
     */
    public function getFormattedWorkTypeAttribute()
    {
        $typeLabels = [
            'ocular_inspection' => 'Ocular Inspection',
            'installation' => 'Installation',
            'replacement' => 'Replacement',
            'repair' => 'Repair',
            'others' => 'Others',
        ];

        $label = $typeLabels[$this->work_type] ?? ucfirst($this->work_type);
        
        $detailsField = match($this->work_type) {
            'ocular_inspection' => 'ocular_details',
            'installation' => 'installation_details',
            'repair' => 'repair_details',
            'replacement' => 'replacement_details',
            'others' => 'others_details',
            default => null,
        };
        
        $details = $detailsField && $this->$detailsField ? $this->$detailsField : 'No details';

        return $label . ': ' . $details;
    }

    /**
     * Get full location string (without campus)
     */
    public function getFullLocationAttribute()
    {
        $parts = array_filter([
            $this->department,
            $this->building_name,
            $this->office_room,
        ]);

        return empty($parts) ? 'Not specified' : implode(' • ', $parts);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'completed' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    /**
     * Check if inspection is scheduled
     */
    public function isInspectionScheduled()
    {
        return !is_null($this->scheduled_date);
    }

    /**
     * Get formatted scheduled date
     */
    public function getFormattedScheduledDateAttribute()
    {
        if ($this->scheduled_date) {
            return $this->scheduled_date->format('F d, Y h:i A');
        }
        return null;
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope by work type
     */
    public function scopeByWorkType($query, $type)
    {
        return $query->where('work_type', $type);
    }

    /**
     * Scope for requests with scheduled inspections
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_date');
    }

    /**
     * Scope for upcoming inspections
     */
    public function scopeUpcomingInspections($query)
    {
        return $query->where('scheduled_date', '>', now());
    }

    public function inspectionReport()
    {
        return $this->hasOne(InspectionReport::class);
    }

/**
 * Check if inspection report exists and is pending
 */
    public function hasPendingInspectionReport()
    {
        return $this->inspectionReport && $this->inspectionReport->status === 'pending';
    }
}