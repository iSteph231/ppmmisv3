<?php

namespace App\Http\Requests;

use App\Models\FacilityRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isUser()
            && $this->route('facilityRequest')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:1,120'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other', 'Prefer not to say'])],
            'client_category' => ['required', Rule::in(['Students', 'Faculty', 'Non-Teaching', 'Supplier', 'Alumni', 'Regulatory Body', 'Industry', 'Community', 'Others'])],
            'other_category' => ['required_if:client_category,Others', 'nullable', 'string', 'max:255'],
            'semester' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:50'],
            'ratings' => ['required', 'array:'.implode(',', array_keys(FacilityRequest::EVALUATION_INDICATORS)), 'size:9'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (FacilityRequest::EVALUATION_INDICATORS as $key => $label) {
            $rules['ratings.'.$key] = ['required', 'integer', 'between:1,5'];
        }

        return $rules;
    }
}