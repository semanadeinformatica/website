<?php

namespace Tests\Unit;

use App\Models\RaffleTicket;
use App\Services\RaffleWinnerSelector;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RaffleWinnerSelectorTest extends TestCase
{
    /**
     * @param  array<int, int>  $quantities
     * @return Collection<int, RaffleTicket>
     */
    private function tickets(array $quantities): Collection
    {
        $tickets = [];
        $enrollmentId = 1;

        foreach ($quantities as $quantity) {
            $tickets[] = new RaffleTicket([
                'enrollment_id' => $enrollmentId++,
                'quantity' => $quantity,
            ]);
        }

        return collect($tickets);
    }

    public function test_it_respects_cumulative_ticket_boundaries(): void
    {
        $tickets = $this->tickets([10, 1]);
        $selector = new RaffleWinnerSelector;

        $this->assertSame(1, $selector->select($tickets, 1));
        $this->assertSame(1, $selector->select($tickets, 10));
        $this->assertSame(2, $selector->select($tickets, 11));
    }

    public function test_it_selects_winners_proportionally(): void
    {
        $tickets = $this->tickets([10, 1]);
        $selector = new RaffleWinnerSelector;
        $counts = [1 => 0, 2 => 0];

        for ($i = 0; $i < 1000; $i++) {
            $counts[$selector->select($tickets)]++;
        }

        $this->assertGreaterThan(800, $counts[1]);
        $this->assertLessThan(980, $counts[1]);
        $this->assertLessThan(200, $counts[2]);
    }

    public function test_it_rejects_an_empty_ticket_pool(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RaffleWinnerSelector)->select(new Collection);
    }

    public function test_it_rejects_zero_quantity_tickets(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RaffleWinnerSelector)->select($this->tickets([0]));
    }
}
