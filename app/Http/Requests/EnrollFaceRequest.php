<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollFaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
        ];
    }
}
