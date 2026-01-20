<?php

namespace Tests\Unit\Values;

use App\Enums\TicketStatus;
use App\Values\TicketQuery;

describe('TicketQuery', function () {
  it('sets defaults when no params provided', function () {
    $query = new TicketQuery([]);

    expect($query->statuses)->toBeEmpty()
      ->and($query->sort)->toBe('id')
      ->and($query->direction)->toBe('asc')
      ->and($query->perPage)->toBe(25);
  });

  it('parses statuses', function () {
    $query = new TicketQuery(['status' => ['open', 'in_progress']]);

    expect($query->statuses)->toHaveCount(2)
      ->and($query->statuses)->toContain(TicketStatus::Open)
      ->and($query->statuses)->toContain(TicketStatus::InProgress);
  });

  it('parses status string', function () {
    $query = new TicketQuery(['status' => 'closed']);

    expect($query->statuses)->toHaveCount(1)
      ->and($query->statuses)->toContain(TicketStatus::Closed);
  });

  it('parses sort ascending', function () {
    $query = new TicketQuery(['sort' => 'created_at']);

    expect($query->sort)->toBe('created_at')
      ->and($query->direction)->toBe('asc');
  });

  it('parses sort descending', function () {
    $query = new TicketQuery(['sort' => '-updated_at']);

    expect($query->sort)->toBe('updated_at')
      ->and($query->direction)->toBe('desc');
  });

  it('ignores invalid sort columns', function () {
    $query = new TicketQuery(['sort' => 'invalid_column']);

    // Should fallback to default
    expect($query->sort)->toBe('id')
      ->and($query->direction)->toBe('asc');
  });

  it('ignores invalid sort columns with direction', function () {
    $query = new TicketQuery(['sort' => '-invalid_column']);

    // Should fallback to default
    expect($query->sort)->toBe('id')
      ->and($query->direction)->toBe('asc');
  });

  it('parses per page', function () {
    $query = new TicketQuery(['per_page' => 50]);

    expect($query->perPage)->toBe(50);
  });

  it('clamps per page min', function () {
    $query = new TicketQuery(['per_page' => 0]);

    expect($query->perPage)->toBe(1);
  });

  it('clamps per page max', function () {
    $query = new TicketQuery(['per_page' => 101]);

    expect($query->perPage)->toBe(100);
  });
});
