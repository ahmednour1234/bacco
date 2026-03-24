<?php

namespace App\Repositories\Admin;

use App\Models\User;

class ProfileRepository
{
    /**
     * Update personal fields on the User model.
     */
    public function updateUser(User $user, array $data): void
    {
        $fields = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'avatar']));

        if (! empty($fields)) {
            $user->update($fields);
        }
    }

    /**
     * Update (or create) the employee profile for the given user.
     */
    public function updateEmployeeProfile(User $user, array $data): void
    {
        $profileFields = array_filter(
            array_intersect_key($data, array_flip([
                'department', 'position', 'national_id', 'hire_date',
            ])),
            fn ($v) => ! is_null($v)
        );

        if (! empty($profileFields)) {
            $user->employeeProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }
    }
}
