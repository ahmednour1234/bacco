<?php

namespace App\Services\Admin;

use App\Helpers\ImageHelper;
use App\Models\User;
use App\Repositories\Admin\ProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepository $profileRepository
    ) {}

    /**
     * Update the employee's profile (personal info, employee info, avatar, password).
     */
    public function update(User $user, array $data, ?UploadedFile $avatarFile = null): void
    {
        // Handle avatar upload
        if ($avatarFile) {
            $data['avatar'] = ImageHelper::uploadAvatar($avatarFile, $user->avatar);
        }

        // Update user core fields
        $this->profileRepository->updateUser($user, $data);

        // Update employee-specific profile fields
        $this->profileRepository->updateEmployeeProfile($user, $data);

        // Update password if provided
        if (! empty($data['new_password'])) {
            $user->update(['password' => Hash::make($data['new_password'])]);
        }
    }
}
