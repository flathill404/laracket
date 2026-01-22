<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\TicketStatus;

class TicketQuery
{
    /** @var array<TicketStatus> */
    public private(set) array $statuses;

    public private(set) string $sort = 'id';

    public private(set) string $direction = 'desc';

    public private(set) int $perPage = 25;

    /**
     * @param  array<mixed>  $params
     */
    public function __construct(array $params)
    {
        /** @var string|array<string>|null $status */
        $status = $params['status'] ?? null;
        $this->statuses = TicketStatus::fromValues($status);

        /** @var mixed $sort */
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

        /** @var mixed $perPageInput */
        $perPageInput = $params['per_page'] ?? 25;
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 25;

        $this->perPage = max(1, min(100, $perPage));
    }
}
