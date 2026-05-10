<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    use HasFactory;

    protected $table = 'inspection_reports';

    protected $fillable = [
        'work_request_id',
        'report_number',
        'scheduled_date',
        'inspection_notes',
        'status',
        'findings',
        'recommendations',
        'actual_inspection_date',
        'inspected_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'actual_inspection_date' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the work request associated with this inspection report
     */
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }

    /**
     * Get the inspector user
     */
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'completed' => 'badge-info',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Check if report is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}