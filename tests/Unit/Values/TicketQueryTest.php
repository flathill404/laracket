<?php

namespace Tests\Unit\Values;

use App\Enums\TicketStatus;
use App\Values\TicketQuery;
use Tests\TestCase;

class TicketQueryTest extends TestCase
{
    public function test_it_sets_defaults_when_no_params_provided()
    {
        $query = new TicketQuery([]);

        $this->assertEmpty($query->statuses);
        $this->assertEquals('id', $query->sort);
        $this->assertEquals('asc', $query->direction);
        $this->assertEquals(25, $query->perPage);
    }

    public function test_it_parses_statuses()
    {
        $query = new TicketQuery(['status' => ['open', 'in_progress']]);

        $this->assertCount(2, $query->statuses);
        $this->assertTrue(in_array(TicketStatus::Open, $query->statuses));
        $this->assertTrue(in_array(TicketStatus::InProgress, $query->statuses));
    }

    public function test_it_parses_status_string()
    {
        $query = new TicketQuery(['status' => 'closed']);

        $this->assertCount(1, $query->statuses);
        $this->assertTrue(in_array(TicketStatus::Closed, $query->statuses));
    }

    public function test_it_parses_sort_ascending()
    {
        $query = new TicketQuery(['sort' => 'created_at']);

        $this->assertEquals('created_at', $query->sort);
        $this->assertEquals('asc', $query->direction);
    }

    public function test_it_parses_sort_descending()
    {
        $query = new TicketQuery(['sort' => '-updated_at']);

        $this->assertEquals('updated_at', $query->sort);
        $this->assertEquals('desc', $query->direction);
    }

    public function test_it_ignores_invalid_sort_columns()
    {
        $query = new TicketQuery(['sort' => 'invalid_column']);

        // Should fallback to default
        $this->assertEquals('id', $query->sort);
        $this->assertEquals('asc', $query->direction);
    }

    public function test_it_ignores_invalid_sort_columns_with_direction()
    {
        $query = new TicketQuery(['sort' => '-invalid_column']);

        // Should fallback to default
        $this->assertEquals('id', $query->sort);
        $this->assertEquals('asc', $query->direction);
    }

    public function test_it_parses_per_page()
    {
        $query = new TicketQuery(['per_page' => 50]);

        $this->assertEquals(50, $query->perPage);
    }

    public function test_it_clamps_per_page_min()
    {
        $query = new TicketQuery(['per_page' => 0]);

        $this->assertEquals(1, $query->perPage);
    }

    public function test_it_clamps_per_page_max()
    {
        $query = new TicketQuery(['per_page' => 101]);

        $this->assertEquals(100, $query->perPage);
    }
}
