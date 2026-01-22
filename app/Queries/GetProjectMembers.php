<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetProjectMembers
{
    /**
     * Get all members of a project (directly assigned + via teams).
     *
     * @return Collection<int, User>
     */
    public function __invoke(Project $project): Collection
    {
        $directUserIds = $project->assignedUsers()->pluck('users.id');

        $teamUserIds = User::query()
            ->whereHas('teams', function ($query) use ($project) {
                $query->whereHas('assignedProjects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
                });
            })
            ->pluck('id');

        $userIds = $directUserIds->merge($teamUserIds)->unique();

        $query = User::whereIn('id', $userIds);

        return $query->get();
    }
}
