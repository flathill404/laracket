<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUserProfile
{
    public function __invoke(User $user, array $data)
    {
        $user->update($data);

        return $user;
    }
}
