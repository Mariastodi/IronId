<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'cpf' => ['sometimes', 'string', 'max:14', 'unique:members,cpf,'.$member->id],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'plan_id' => ['sometimes', 'exists:plans,id'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
