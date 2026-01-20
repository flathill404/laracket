<?php

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GetUserTickets
{
    /**
     * @param  array<\App\Enums\TicketStatus>  $statuses
     */
    public function __invoke(User $user, array $statuses = [], ?string $sort = 'id', int $perPage = 25): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($user) {
                /** @var Builder<Project> $query */
                // @phpstan-ignore varTag.nativeType
                $query->visibleToUser($user);
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($statuses, fn (Builder $query) => $query->whereIn('status', $statuses));

        // Sorting
        $allowedSorts = ['id', 'created_at', 'updated_at', 'due_date'];
        $direction = 'asc';

        if ($sort) {
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction);
            }
        }

        // Ensure deterministic order
        if ($sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($perPage);
    }
}
