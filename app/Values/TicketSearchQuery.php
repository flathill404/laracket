<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\TicketStatus;

class TicketSearchQuery
{
    public private(set) string $keyword;

    public private(set) ?string $projectId;

    /** @var array<TicketStatus> */
    public private(set) array $statuses;

    public private(set) int $perPage;

    /**
     * @param  array<mixed>  $params
     */
    public function __construct(array $params)
    {
        /** @var mixed $q */
        $q = $params['q'] ?? '';
        $this->keyword = is_string($q) ? trim($q) : '';

        /** @var mixed $projectId */
        $projectId = $params['project_id'] ?? null;
        $this->projectId = is_string($projectId) ? $projectId : null;

        /** @var string|array<string>|null $status */
        $status = $params['status'] ?? null;
        $this->statuses = TicketStatus::fromValues($status);

        /** @var mixed $perPageInput */
        $perPageInput = $params['per_page'] ?? 25;
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 25;

        $this->perPage = max(1, min(100, $perPage));
    }
}
