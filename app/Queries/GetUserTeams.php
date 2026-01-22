<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserTeams
{
    /**
     * @return Collection<int,Team>
     */
    public function __invoke(User $user): Collection
    {
        $query = Team::query()
            ->where(function ($query) use ($user) {
                // Direct Membership
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });

                // Organization Admin/Owner Access
                $query->orWhereHas('organization', function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                        ->orWhereHas('users', function ($mq) use ($user) {
                            $mq->where('user_id', $user->id)
                                ->where('role', \App\Enums\OrganizationRole::Admin);
                        });
                });
            });

        return $query->get();
    }
}
