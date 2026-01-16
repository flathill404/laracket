<?php

namespace App\Queries;

use App\Enums\OrganizationRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserProjects
{
    /**
     * @return Collection<int,Project>
     */
    public function __invoke(User $user): Collection
    {
        return Project::query()
            ->where(function ($query) use ($user) {
                // Organization Admin/Owner Access
                $query->whereHas('organization', function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                        ->orWhereHas('users', function ($mq) use ($user) {
                            $mq->where('user_id', $user->id)
                                ->where('role', OrganizationRole::Admin);
                        });
                });

                // Direct Assignment
                $query->orWhereHas('assignedUsers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });

                // Team Assignment
                $query->orWhereHas('assignedTeams.members', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->get();
    }
}
