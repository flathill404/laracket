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
     * @param  array<string>  $statuses
     * @return Collection<int, Ticket>
     */
    public function __invoke(User $user, array $statuses = []): Collection
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($user) {
                /** @var Builder<Project> $query */
                // @phpstan-ignore varTag.nativeType
                $query->visibleToUser($user);
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($statuses, fn (Builder $query) => $query->whereIn('status', $statuses));

        return $query->get();
    }
}
