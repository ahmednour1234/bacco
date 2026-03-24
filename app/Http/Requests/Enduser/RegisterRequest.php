<?php

namespace App\Http\Requests\Enduser;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'role'     => ['required', 'string', 'max:100'],
            'company'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'terms'    => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        ];
    }
}
