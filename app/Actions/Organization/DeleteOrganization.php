<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteOrganization
{
    public function __invoke(User $actor, Organization $organization): bool
    {
        $deleted = DB::transaction(function () use ($organization) {
            return (bool) $organization->delete();
        });

        return $deleted;
    }
}
