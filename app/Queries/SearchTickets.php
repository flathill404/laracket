<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Values\TicketSearchQuery;
use Illuminate\Contracts\Pagination\CursorPaginator;

class SearchTickets
{
    /**
     * @return CursorPaginator<int, Ticket>
     */
    public function __invoke(User $user, TicketSearchQuery $searchQuery): CursorPaginator
    {
        $accessibleProjectIds = Project::query()
            ->visibleToUser($user)
            ->pluck('id');

        $scoutBuilder = Ticket::search($searchQuery->keyword)
            ->whereIn('project_id', $accessibleProjectIds->toArray());

        if ($searchQuery->projectId !== null) {
            $scoutBuilder->where('project_id', $searchQuery->projectId);
        }

        if (! empty($searchQuery->statuses)) {
            $statusValues = array_map(fn ($status) => $status->value, $searchQuery->statuses);
            $scoutBuilder->whereIn('status', $statusValues);
        }

        $ids = $scoutBuilder->keys();

        return Ticket::query()
            ->whereIn('id', $ids)
            ->with(['assignees', 'reviewers', 'project'])
            ->orderBy('id', 'asc')
            ->cursorPaginate($searchQuery->perPage);
    }
}
