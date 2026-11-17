<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacilityAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isUser();
    }

    public function rules(): array
    {
        return [
            'requested_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'requested_time' => ['required', 'date_format:H:i'],
        ];
    }
}
