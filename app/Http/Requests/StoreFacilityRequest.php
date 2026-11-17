<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['requested_by' => $this->user()?->name]);
    }

    public function authorize(): bool
    {
        return $this->user()->isUser();
    }

    public function rules(): array
    {
        return [
            'program_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'facility' => ['required', 'string', 'max:255', Rule::in(config('facilities.options'))],
            'requested_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'requested_time' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:2000'],
            'lead_person' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/\A09[0-9]{9}\z/'],
            'participants' => ['required', 'string', 'max:10000'],
            'requested_by' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_number.regex' => 'The contact number must be exactly 11 digits and start with 09.',
        ];
    }
}
