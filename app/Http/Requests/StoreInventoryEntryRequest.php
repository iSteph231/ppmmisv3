<?php

namespace App\Http\Requests;

use App\Support\InventoryForms;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $fields = array_merge(...array_values(InventoryForms::sections($this->route('form'))));
        $rules = ['data' => ['required', 'array:'.implode(',', array_keys($fields))]];

        foreach ($fields as $key => $field) {
            $rules['data.'.$key] = [
                $field['required'] ? 'required' : 'nullable',
                ...match ($field['type']) {
                    'number' => ['numeric', 'gt:0', 'max:10000'],
                    'date' => ['date_format:Y-m-d'],
                    'checkbox' => ['boolean'],
                    default => ['string', 'max:255'],
                },
            ];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [];
        foreach (InventoryForms::sections($this->route('form')) as $section => $fields) {
            foreach ($fields as $key => $field) {
                $attributes['data.'.$key] = $section.' — '.$field['label'];
            }
        }

        return $attributes;
    }
}
