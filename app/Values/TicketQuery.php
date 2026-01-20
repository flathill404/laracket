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
        if (is_string($sort)) {
            if (str_starts_with($sort, '-')) {
                $this->direction = 'desc';
                $this->sort = substr($sort, 1);
            } else {
                $this->sort = $sort;
            }
        }

        $perPage = (int) ($params['per_page'] ?? 25);
        $this->perPage = max(1, min(100, $perPage));
    }
}
