<?php

namespace App\Queries;

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
            ->visibleToUser($user)
            ->get();
    }
}
