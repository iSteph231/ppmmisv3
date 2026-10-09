<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FacilityRequest extends Model
{
    use HasFactory;

    public const EVALUATION_INDICATORS = [
        'ventilation' => 'Well ventilated.',
        'lighting' => 'Well lighted.',
        'safety' => 'Safe and comfortable.',
        'space' => 'Enough space.',
        'comfort_rooms' => 'Comfort rooms are accessible.',
        'signage' => 'Signages are visible.',
        'fire_extinguishers' => 'Fire extinguishers are visible and ready to use.',
        'chairs' => 'Numbers of chairs are enough.',
        'water' => 'Water supply is available.',
    ];

    protected $fillable = [
        'user_id',
        'request_number',
        'facility',
        'category',
        'requested_date',
        'purpose',
        'requested_time',
        'lead_person',
        'contact_number',
        'participants',
        'requested_by',
        'status',
        'program_image_path',
        'decline_reason',
        'before_photo_path',
        'after_photo_path',
        'evaluation',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'evaluation' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photoDirectory(): string
    {
        $requester = Str::slug(Str::limit($this->user->name, 60, '')) ?: 'requester';
        $facility = Str::slug(Str::limit($this->facility, 60, '')) ?: 'facility';

        return 'facility-requests/'.$requester.' - '.$facility.' - '.$this->request_number;
    }

    public function scopeReservedAt(Builder $query, string $date, string $time): Builder
    {
        return $query->whereDate('requested_date', $date)
            ->whereTime('requested_time', $time.':00')
            ->whereIn('status', ['pending', 'approved']);
    }
}
