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
        'photo_path',
        'department',
        'building_name',
        'office_room',
        'work_type',
        'ocular_details',
        'installation_details',
        'repair_details',
        'replacement_details',
        'others_details',
        'status',
        'admin_notes',
        'completed_at',
        'scheduled_date',
        'inspection_notes',
        'priority',
    ];

    protected $casts = [
        'requester_snapshot' => 'array',
        'completed_at' => 'datetime',
        'scheduled_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_COMPLETED = 'completed';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRequesterDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->requester_snapshot['name'] ?? $this->requester_name ?? 'N/A';
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

        $detailsField = match ($this->work_type) {
            'ocular_inspection' => 'ocular_details',
            'installation' => 'installation_details',
            'repair' => 'repair_details',
            'replacement' => 'replacement_details',
            'others' => 'others_details',
            default => null,
        };

        $details = $detailsField && $this->$detailsField ? $this->$detailsField : 'No details';

        return $label.': '.$details;
    }

    public function getFullLocationAttribute()
    {
        $parts = array_filter([
            $this->department,
            $this->building_name,
            $this->office_room,
        ]);

        return empty($parts) ? 'Not specified' : implode(' • ', $parts);
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'completed' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    public function isInspectionScheduled()
    {
        return ! is_null($this->scheduled_date);
    }

    public function getFormattedScheduledDateAttribute()
    {
        if ($this->scheduled_date) {
            return $this->scheduled_date->format('F d, Y h:i A');
        }

        return null;
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

    public function scopeByWorkType($query, $type)
    {
        return $query->where('work_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_date');
    }

    public function scopeUpcomingInspections($query)
    {
        return $query->where('scheduled_date', '>', now());
    }

    public function inspectionReport()
    {
        return $this->hasOne(InspectionReport::class);
    }

    public function hasPendingInspectionReport()
    {
        return $this->inspectionReport && $this->inspectionReport->status === 'pending';
    }
}
