<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enduser\UpdateProfileRequest;
use App\Services\Enduser\ProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    public function show(): View
    {
        $user    = Auth::user()->load('clientProfile');
        $profile = $user->clientProfile;

        return view('enduser.profile', compact('user', 'profile'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->profileService->update(
            $user,
            $request->validated(),
            $request->hasFile('avatar') ? $request->file('avatar') : null
        );

        return redirect()->route('enduser.profile')->with('success', 'Profile updated successfully.');
    }
}
