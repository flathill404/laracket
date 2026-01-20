<?php

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Values\TicketQuery;
use Illuminate\Database\Eloquent\Builder;

class GetUserTickets
{
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
        $allowedSorts = ['id', 'created_at', 'updated_at', 'due_date'];

        if ($ticketQuery->sort && in_array($ticketQuery->sort, $allowedSorts)) {
            $query->orderBy($ticketQuery->sort, $ticketQuery->direction);
        }

        // Ensure deterministic order
        if ($ticketQuery->sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($ticketQuery->perPage);
    }
}
