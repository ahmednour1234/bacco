<?php

namespace App\Repositories\Admin;

use App\Enums\UserTypeEnum;
use App\Models\User;

class AuthRepository
{
    /**
     * Find a user by email whose type is admin or employee.
     */
    public function findAdminOrEmployeeByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->whereIn('user_type', [UserTypeEnum::Admin, UserTypeEnum::Employee])
            ->first();
    }

    /**
     * Determine whether a user is allowed to access the admin panel.
     */
    public function isAuthorized(User $user): bool
    {
        return $user->user_type === UserTypeEnum::Admin
            || $user->user_type === UserTypeEnum::Employee;
    }
}
