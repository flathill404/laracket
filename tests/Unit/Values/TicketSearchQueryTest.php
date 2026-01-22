<?php

declare(strict_types=1);

namespace Tests\Unit\Values;

use App\Enums\TicketStatus;
use App\Values\TicketSearchQuery;

describe('TicketSearchQuery', function () {
    it('sets defaults when no params provided', function () {
        $query = new TicketSearchQuery([]);

        expect($query->keyword)->toBe('')
            ->and($query->projectId)->toBeNull()
            ->and($query->statuses)->toBeEmpty()
            ->and($query->perPage)->toBe(25);
    });

    it('parses keyword', function () {
        $query = new TicketSearchQuery(['q' => 'search term']);

        expect($query->keyword)->toBe('search term');
    });

    it('trims keyword', function () {
        $query = new TicketSearchQuery(['q' => '  search term  ']);

        expect($query->keyword)->toBe('search term');
    });

    it('handles non-string keyword', function () {
        $query = new TicketSearchQuery(['q' => 123]);

        expect($query->keyword)->toBe('');
    });

    it('parses project_id', function () {
        $query = new TicketSearchQuery(['project_id' => 'uuid-1234']);

        expect($query->projectId)->toBe('uuid-1234');
    });

    it('handles non-string project_id', function () {
        $query = new TicketSearchQuery(['project_id' => 123]);

        expect($query->projectId)->toBeNull();
    });

    it('parses statuses', function () {
        $query = new TicketSearchQuery(['status' => ['open', 'in_progress']]);

        expect($query->statuses)->toHaveCount(2)
            ->and($query->statuses)->toContain(TicketStatus::Open)
            ->and($query->statuses)->toContain(TicketStatus::InProgress);
    });

    it('parses status string', function () {
        $query = new TicketSearchQuery(['status' => 'closed']);

        expect($query->statuses)->toHaveCount(1)
            ->and($query->statuses)->toContain(TicketStatus::Closed);
    });

    it('parses per page', function () {
        $query = new TicketSearchQuery(['per_page' => 50]);

        expect($query->perPage)->toBe(50);
    });

    it('clamps per page min', function () {
        $query = new TicketSearchQuery(['per_page' => 0]);

        expect($query->perPage)->toBe(1);
    });

    it('clamps per page max', function () {
        $query = new TicketSearchQuery(['per_page' => 101]);

        expect($query->perPage)->toBe(100);
    });

    it('handles non-numeric per page', function () {
        $query = new TicketSearchQuery(['per_page' => 'invalid']);

        expect($query->perPage)->toBe(25);
    });
});
