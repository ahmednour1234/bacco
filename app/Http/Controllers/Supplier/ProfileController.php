<?php

namespace App\Http\Controllers\Supplier;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user    = Auth::user()->load('supplierProfile');
        $profile = $user->supplierProfile;

        return view('supplier.profile', compact('user', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name'                     => ['required', 'string', 'max:255'],
            'email'                    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'                    => ['nullable', 'string', 'max:30'],
            'avatar'                   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Company info
            'company_name'             => ['nullable', 'string', 'max:255'],
            'trade_name'               => ['nullable', 'string', 'max:255'],
            'division'                 => ['nullable', 'string', 'max:100'],
            'cr_number'                => ['nullable', 'string', 'max:100'],
            'vat_number'               => ['nullable', 'string', 'max:100'],
            'address'                  => ['nullable', 'string', 'max:500'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'country'                  => ['nullable', 'string', 'max:100'],
            // Password change
            'current_password'         => ['nullable', 'string'],
            'new_password'             => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Avatar upload
        if ($request->hasFile('avatar')) {
            $data['avatar'] = ImageHelper::uploadAvatar($request->file('avatar'), $user->avatar);
            $user->update(['avatar' => $data['avatar']]);
        }

        // User core fields
        $user->update($request->only('name', 'email', 'phone'));

        // Supplier profile fields
        $profileFields = array_filter(
            $request->only([
                'company_name', 'trade_name', 'division',
                'cr_number', 'vat_number', 'address', 'city', 'country',
            ]),
            fn ($v) => ! is_null($v)
        );

        if (! empty($profileFields)) {
            $user->supplierProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        // Password change
        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return redirect()->route('supplier.profile')->with('success', 'Profile updated successfully.');
    }
}
