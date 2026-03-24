<?php

namespace App\Services\Enduser;

use App\Helpers\ImageHelper;
use App\Models\User;
use App\Repositories\Enduser\ProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepository $profileRepository
    ) {}

    /**
     * Update the user's profile (personal info, business info, and optionally avatar + password).
     */
    public function update(User $user, array $data, ?UploadedFile $avatarFile = null): void
    {
        // Handle avatar upload via helper
        if ($avatarFile) {
            $data['avatar'] = ImageHelper::uploadAvatar($avatarFile, $user->avatar);
        }

        // Update user fields
        $this->profileRepository->updateUser($user, $data);

        // Update client profile fields
        $this->profileRepository->updateClientProfile($user, $data);

        // Update password if provided
        if (! empty($data['new_password'])) {
            $user->update(['password' => Hash::make($data['new_password'])]);
        }
    }
}
