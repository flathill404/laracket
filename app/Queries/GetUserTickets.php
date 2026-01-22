<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Values\TicketQuery;
use Illuminate\Database\Eloquent\Builder;

class GetUserTickets
{
    /**
     * @return \Illuminate\Contracts\Pagination\CursorPaginator<int, \App\Models\Ticket>
     */
    public function __invoke(User $user, TicketQuery $ticketQuery): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($user) {
                /** @var Builder<Project> $query */
                // @phpstan-ignore varTag.nativeType
                $query->visibleToUser($user);
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($ticketQuery->statuses, fn (Builder $query) => $query->whereIn('status', $ticketQuery->statuses));

        // Sorting
        if ($ticketQuery->sort) {
            $query->orderBy($ticketQuery->sort, $ticketQuery->direction);
        }

        // Ensure deterministic order
        if ($ticketQuery->sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($ticketQuery->perPage);
    }
}
