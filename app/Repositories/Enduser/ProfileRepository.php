<?php

namespace App\Repositories\Enduser;

use App\Models\User;

class ProfileRepository
{
    /**
     * Update personal fields on the User model.
     * Only fields present in $data are written; absent keys are left untouched.
     */
    public function updateUser(User $user, array $data): void
    {
        $fields = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'avatar']));

        if (! empty($fields)) {
            $user->update($fields);
        }
    }

    /**
     * Update (or create) the client profile for the given user.
     */
    public function updateClientProfile(User $user, array $data): void
    {
        $profileFields = array_filter(
            array_intersect_key($data, array_flip([
                'company_name', 'trade_name', 'cr_number',
                'vat_number', 'address', 'city', 'country',
            ])),
            fn ($v) => ! is_null($v)
        );

        if (! empty($profileFields)) {
            $user->clientProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }
    }
}
