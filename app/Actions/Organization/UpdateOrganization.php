<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateOrganization
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, Organization $organization, array $data): Organization
    {
        return DB::transaction(function () use ($organization, $data) {
            $organization->update([
                'name' => $data['name'] ?? $organization->name,
                'display_name' => $data['display_name'] ?? $organization->display_name,
            ]);

            return $organization;
        });
    }
}
