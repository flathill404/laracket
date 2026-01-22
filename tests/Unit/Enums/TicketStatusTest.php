<?php

declare(strict_types=1);

use App\Enums\TicketStatus;

describe('fromValues', function () {
    it('returns empty array when input is null', function () {
        $result = TicketStatus::fromValues(null);
        expect($result)->toBeEmpty();
    });

    it('returns empty array when input is empty array', function () {
        $result = TicketStatus::fromValues([]);
        expect($result)->toBeEmpty();
    });

    it('returns single status when input is valid string', function () {
        $result = TicketStatus::fromValues('open');
        expect($result)
            ->toHaveCount(1)
            ->and($result[0])->toBe(TicketStatus::Open);
    });

    it('returns multiple statuses when input is array of valid strings', function () {
        $result = TicketStatus::fromValues(['open', 'closed']);
        expect($result)
            ->toHaveCount(2)
            ->and($result[0])->toBe(TicketStatus::Open)
            ->and($result[1])->toBe(TicketStatus::Closed);
    });

    it('filters out invalid statuses', function () {
        $result = TicketStatus::fromValues(['open', 'invalid_status', 'closed']);
        expect($result)
            ->toHaveCount(2)
            ->and($result[0])->toBe(TicketStatus::Open)
            ->and($result[1])->toBe(TicketStatus::Closed);
    });

    it('returns empty array when all inputs are invalid', function () {
        $result = TicketStatus::fromValues(['invalid_1', 'invalid_2']);
        expect($result)->toBeEmpty();
    });
});
