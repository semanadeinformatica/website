<?php

namespace App\Services;

use App\Models\RaffleTicket;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

class RaffleWinnerSelector
{
    /**
     * @param  Collection<int, RaffleTicket>  $tickets
     */
    public function select(Collection $tickets, ?int $roll = null): int
    {
        $total = (int) $tickets->sum(
            fn (RaffleTicket $ticket) => (int) $ticket->quantity
        );

        if ($total < 1) {
            throw new InvalidArgumentException('No tickets to draw from.');
        }

        $roll ??= random_int(1, $total);

        if ($roll < 1 || $roll > $total) {
            throw new InvalidArgumentException('Roll outside the ticket pool.');
        }

        $cumulative = 0;

        foreach ($tickets as $ticket) {
            $cumulative += (int) $ticket->quantity;

            if ($roll <= $cumulative) {
                return (int) $ticket->enrollment_id;
            }
        }

        throw new LogicException('Weighted raffle selection failed.');
    }
}
