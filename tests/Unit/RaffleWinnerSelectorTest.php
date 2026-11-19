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
     * @param  array<int, int>  $weights
     * @return Collection<int, array{enrollment_id: int, weight: int}>
     */
    private function entries(array $weights): Collection
    {
        $entries = [];
        $enrollmentId = 1;

        foreach ($weights as $weight) {
            $entries[] = [
                'enrollment_id' => $enrollmentId++,
                'weight' => $weight,
            ];
        }

        return collect($entries);
    }

    public function test_it_respects_cumulative_weight_boundaries(): void
    {
        $entries = $this->entries([10, 1]);
        $selector = new RaffleWinnerSelector;

        $this->assertSame(1, $selector->select($entries, 1));
        $this->assertSame(1, $selector->select($entries, 10));
        $this->assertSame(2, $selector->select($entries, 11));
    }

    public function test_it_selects_winners_proportionally_to_points(): void
    {
        $entries = $this->entries([10, 1]);
        $selector = new RaffleWinnerSelector;
        $counts = [1 => 0, 2 => 0];

        for ($i = 0; $i < 1000; $i++) {
            $counts[$selector->select($entries)]++;
        }

        $this->assertGreaterThan(800, $counts[1]);
        $this->assertLessThan(980, $counts[1]);
        $this->assertLessThan(200, $counts[2]);
    }

    public function test_it_still_accepts_legacy_ticket_models(): void
    {
        $tickets = collect([
            new RaffleTicket(['enrollment_id' => 1, 'quantity' => 3]),
            new RaffleTicket(['enrollment_id' => 2, 'quantity' => 1]),
        ]);

        $selector = new RaffleWinnerSelector;

        $this->assertSame(1, $selector->select($tickets, 3));
        $this->assertSame(2, $selector->select($tickets, 4));
    }

    public function test_it_rejects_an_empty_pool(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RaffleWinnerSelector)->select(new Collection);
    }

    public function test_it_rejects_zero_weight_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RaffleWinnerSelector)->select($this->entries([0]));
    }
}
