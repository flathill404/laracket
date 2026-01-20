<?php

namespace App\Values;

use App\Enums\TicketStatus;

class TicketQuery
{
    /** @var array<TicketStatus> */
    public private(set) array $statuses;

    public private(set) string $sort = 'id';

    public private(set) string $direction = 'asc';

    public private(set) int $perPage = 25;

    /**
     * @param  array<string, mixed>  $params
     */
    public function __construct(array $params)
    {
        $this->statuses = TicketStatus::fromValues($params['status'] ?? null);

        $sort = $params['sort'] ?? null;
        $allowedSorts = ['id', 'created_at', 'updated_at', 'due_date'];

        if (is_string($sort)) {
            $column = $sort;
            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $column = substr($sort, 1);
            }

            if (in_array($column, $allowedSorts)) {
                $this->sort = $column;
                $this->direction = $direction;
            }
        }

        $perPage = (int) ($params['per_page'] ?? 25);
        $this->perPage = max(1, min(100, $perPage));
    }
}
