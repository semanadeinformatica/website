<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaffleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_raffle_draw_can_be_created_and_accessed(): void
    {
        $edition = Edition::factory()->create();
        $winner = Enrollment::factory()->create(['edition_id' => $edition->id]);

        $draw = RaffleDraw::create([
            'edition_id' => $edition->id,
            'name' => 'Mechanical Keyboard Draw',
            'description' => 'Win a Keychron K2 keyboard',
            'ticket_price' => 25,
            'is_active' => true,
            'draw_at' => now()->addDays(2),
            'winner_enrollment_id' => $winner->id,
        ]);

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'name' => 'Mechanical Keyboard Draw',
            'ticket_price' => 25,
            'is_active' => true,
        ]);

        $this->assertTrue($draw->edition->is($edition));
        $this->assertTrue($draw->winner->is($winner));
        $this->assertEquals(['name' => 'Mechanical Keyboard Draw'], $draw->toSearchableArray());
    }

    public function test_raffle_tickets_and_total_tickets_accessor(): void
    {
        $edition = Edition::factory()->create();
        $draw = RaffleDraw::factory()->create(['edition_id' => $edition->id]);
        $enrollment1 = Enrollment::factory()->create(['edition_id' => $edition->id]);
        $enrollment2 = Enrollment::factory()->create(['edition_id' => $edition->id]);

        $ticket1 = RaffleTicket::create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment1->id,
            'quantity' => 3,
        ]);

        $ticket2 = RaffleTicket::create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment2->id,
            'quantity' => 2,
        ]);

        $this->assertTrue($ticket1->draw->is($draw));
        $this->assertTrue($ticket1->enrollment->is($enrollment1));

        // Total tickets accessor
        $this->assertEquals(5, $draw->total_tickets);

        // When relation is loaded
        $draw->load('tickets');
        $this->assertEquals(5, $draw->total_tickets);

        // Reverse relationships
        $this->assertCount(1, $enrollment1->raffleTickets);
        $this->assertEquals(3, $enrollment1->raffleTickets->first()->quantity);
        $this->assertCount(1, $edition->raffleDraws);
    }

    public function test_raffle_tickets_enforces_unique_draw_and_enrollment(): void
    {
        $edition = Edition::factory()->create();
        $draw = RaffleDraw::factory()->create(['edition_id' => $edition->id]);
        $enrollment = Enrollment::factory()->create(['edition_id' => $edition->id]);

        RaffleTicket::create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => 1,
        ]);

        $this->expectException(QueryException::class);

        RaffleTicket::create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => 2,
        ]);
    }

    public function test_winner_enrollment_nulls_on_delete(): void
    {
        $edition = Edition::factory()->create();
        $winner = Enrollment::factory()->create(['edition_id' => $edition->id]);
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $edition->id,
            'winner_enrollment_id' => $winner->id,
        ]);

        $winner->delete();

        $draw->refresh();
        $this->assertNull($draw->winner_enrollment_id);
    }
}
