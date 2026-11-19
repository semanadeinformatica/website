<?php

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

class RaffleWinnerSelector
{
    /**
     * Pick one enrollment from a weighted pool.
     *
     * Entries may be arrays with `enrollment_id`/`weight` keys or objects
     * exposing the same attributes (RaffleTicket's `quantity` is accepted for
     * backwards compatibility with the legacy ticket pool).
     *
     * @param  Collection<int, mixed>  $entries
     */
    public function select(Collection $entries, ?int $roll = null): int
    {
        $normalized = $entries->map(function (mixed $entry): array {
            if (is_array($entry)) {
                return [
                    'enrollment_id' => (int) $entry['enrollment_id'],
                    'weight' => (int) ($entry['weight'] ?? 0),
                ];
            }

            return [
                'enrollment_id' => (int) $entry->enrollment_id,
                'weight' => (int) ($entry->weight ?? $entry->quantity ?? 0),
            ];
        });

        $total = (int) $normalized->sum('weight');

        if ($total < 1) {
            throw new InvalidArgumentException('No entries to draw from.');
        }

        $roll ??= random_int(1, $total);

        if ($roll < 1 || $roll > $total) {
            throw new InvalidArgumentException('Roll outside the entry pool.');
        }

        $cumulative = 0;

        foreach ($normalized as $entry) {
            $cumulative += $entry['weight'];

            if ($roll <= $cumulative) {
                return $entry['enrollment_id'];
            }
        }

        throw new LogicException('Weighted raffle selection failed.');
    }
}
