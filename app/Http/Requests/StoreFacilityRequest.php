<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isUser();
    }

    public function rules(): array
    {
        return [
            'program_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'facility' => ['required', 'string', 'max:255'],
            'requested_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'requested_time' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:2000'],
            'lead_person' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50', 'regex:/^[0-9+() .-]{7,50}$/'],
            'participants' => ['required', 'string', 'max:10000'],
            'requested_by' => ['required', 'string', 'max:255'],
        ];
    }
}
