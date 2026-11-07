<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAccountRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['role' => ['required', 'in:user,engineer']];
    }
}
