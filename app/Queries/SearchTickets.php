<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Values\TicketSearchQuery;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class SearchTickets
{
    /**
     * @return Paginator<int, Ticket>
     */
    public function __invoke(User $user, TicketSearchQuery $searchQuery): Paginator
    {
        $accessibleProjectIds = Project::query()
            ->visibleToUser($user)
            ->pluck('id');

        $builder = Ticket::search($searchQuery->keyword)
            ->whereIn('project_id', $accessibleProjectIds->toArray());

        if ($searchQuery->projectId !== null) {
            $builder->where('project_id', $searchQuery->projectId);
        }

        if (! empty($searchQuery->statuses)) {
            $statusValues = array_map(fn($status) => $status->value, $searchQuery->statuses);
            $builder->whereIn('status', $statusValues);
        }

        return $builder
            ->query(fn(Builder $query) => $query->with(['assignees', 'reviewers', 'project']))
            ->cursorPaginate($searchQuery->perPage);
    }
}
