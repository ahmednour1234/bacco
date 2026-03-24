<?php

namespace App\Http\Requests\Enduser;

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
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:150', "unique:users,email,{$userId}"],
            'phone'        => ['nullable', 'string', 'max:30'],

            // Avatar
            'avatar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Business info (client profile)
            'company_name' => ['nullable', 'string', 'max:150'],
            'trade_name'   => ['nullable', 'string', 'max:150'],
            'cr_number'    => ['nullable', 'string', 'max:50'],
            'vat_number'   => ['nullable', 'string', 'max:50'],
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],

            // Password change (all three required together, or none)
            'current_password'      => ['nullable', 'string', 'current_password'],
            'new_password'          => ['nullable', 'string', Password::min(8), 'confirmed', 'required_with:current_password'],
            'new_password_confirmation' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.max'              => 'Profile photo must be less than 2 MB.',
            'avatar.mimes'            => 'Profile photo must be a JPG, PNG, or WebP file.',
            'current_password.current_password' => 'The current password is incorrect.',
            'new_password.required_with' => 'Please enter a new password.',
        ];
    }
}
