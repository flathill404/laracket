<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrganization
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $creator, array $data): Organization
    {
        return DB::transaction(function () use ($creator, $data) {
            /** @var Organization $organization */
            $organization = Organization::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'owner_user_id' => $creator->id,
            ]);

            // Add creator as admin member
            $organization->users()->attach($creator->id, ['role' => 'admin']);

            return $organization;
        });
    }
}
