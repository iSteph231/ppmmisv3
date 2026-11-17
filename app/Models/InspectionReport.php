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
        'observation',
        'recommendations',
        'actual_inspection_date',
        'inspected_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'actual_inspection_date' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'completed' => 'badge-info',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
