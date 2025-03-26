<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'unique:users', 'max:100'],
            'password' => ['required', 'max:100'],
            'name' => ['required', 'max:100'],
        ];
    }
}

