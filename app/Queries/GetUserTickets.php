<?php

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GetUserTickets
{
    /**
     * @return Collection<int, Ticket>
     */
    public function __invoke(User $user): Collection
    {
        return Ticket::query()
            ->whereHas('project', function ($query) use ($user) {
                /** @var Builder<Project> $query */
                // @phpstan-ignore varTag.nativeType
                $query->visibleToUser($user);
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->get();
    }
}
