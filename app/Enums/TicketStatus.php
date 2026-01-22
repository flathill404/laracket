<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Resolved = 'resolved';
    case Closed = 'closed';

    /**
     * @param  string|array<string>|null  $values
     * @return array<self>
     */
    public static function fromValues(string|array|null $values): array
    {
        $values = (array) $values;

        return collect($values)
            ->map(fn (string $s) => self::tryFrom($s))
            ->filter()
            ->values()
            ->all();
    }
}
