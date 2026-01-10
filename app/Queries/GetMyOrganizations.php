<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetMyOrganizations
{
    /**
     * @return Collection<int, \App\Models\Organization>
     */
    public function __invoke(User $user): Collection
    {
        // Assuming relationship exists
        return $user->organizations;
    }
}
