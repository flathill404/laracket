<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteOrganization
{
    public function __invoke(User $actor, Organization $organization): bool
    {
        return DB::transaction(function () use ($organization) {
            return (bool) $organization->delete();
        });
    }
}
