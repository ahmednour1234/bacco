<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Services\Admin\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    public function show(): View
    {
        $user    = Auth::user()->load('employeeProfile');
        $profile = $user->employeeProfile;

        return view('admin.profile', compact('user', 'profile'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->update(
            Auth::user(),
            $request->validated(),
            $request->hasFile('avatar') ? $request->file('avatar') : null
        );

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
}
