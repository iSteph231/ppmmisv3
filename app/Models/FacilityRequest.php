<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FacilityRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_number',
        'facility',
        'category',
        'requested_date',
        'purpose',
        'status',
        'before_photo_path',
        'after_photo_path',
    ];

    protected $casts = [
        'requested_date' => 'date',
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
}
