<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            // Personal info
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', "unique:users,email,{$userId}"],
            'phone'       => ['nullable', 'string', 'max:30'],

            // Avatar
            'avatar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Employee profile
            'department'  => ['nullable', 'string', 'max:100'],
            'position'    => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'hire_date'   => ['nullable', 'date'],

            // Password change
            'current_password'          => ['nullable', 'string', 'current_password'],
            'new_password'              => ['nullable', 'string', Password::min(8), 'confirmed', 'required_with:current_password'],
            'new_password_confirmation' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.max'                        => 'Profile photo must be less than 2 MB.',
            'avatar.mimes'                      => 'Profile photo must be a JPG, PNG, or WebP file.',
            'current_password.current_password' => 'The current password is incorrect.',
            'new_password.required_with'        => 'Please enter a new password.',
        ];
    }
}
