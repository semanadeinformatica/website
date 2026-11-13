<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RaffleDrawAdminTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'edition_id' => $this->edition->id,
            'name' => 'Sorteio de teste',
            'description' => 'Descrição do sorteio',
            'ticket_price' => 25,
            'is_active' => true,
            'draw_at' => now()->addDay()->toDateTimeString(),
        ], $overrides);
    }

    public function test_admin_can_list_raffle_draws(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => 'Teclado Mecânico',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('CRUD/RaffleDraws/Index', false)
            ->has('items.data', 1)
            ->where('items.data.0.id', $draw->id)
            ->where('items.data.0.name', 'Teclado Mecânico')
            ->has('with.editions', 1)
        );
    }

    public function test_admin_index_loads_the_winner_relation(): void
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
        ]);

        RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'winner_enrollment_id' => $enrollment->id,
            'is_active' => false,
            'draw_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('CRUD/RaffleDraws/Index', false)
            ->where('items.data.0.winner.id', $enrollment->id)
            ->where('items.data.0.winner.participant.user.name', $participant->user->name)
        );
    }

    public function test_admin_can_create_a_raffle_draw(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload());

        $response->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'edition_id' => $this->edition->id,
            'name' => 'Sorteio de teste',
            'ticket_price' => 25,
            'is_active' => true,
        ]);
    }

    public function test_create_validates_required_raffle_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['edition_id' => 99999]))
            ->assertSessionHasErrors('edition_id');

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['ticket_price' => 0]))
            ->assertSessionHasErrors('ticket_price');

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload([
                'draw_at' => now()->subDay()->toDateTimeString(),
            ]))
            ->assertSessionHasErrors('draw_at');
    }

    public function test_admin_can_update_an_active_raffle_draw(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => 'Nome antigo',
            'is_active' => true,
            'draw_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'name' => 'Nome novo',
                'ticket_price' => 50,
            ]));

        $response->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'name' => 'Nome novo',
            'ticket_price' => 50,
        ]);
    }

    public function test_active_draw_rejects_a_past_draw_date(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'is_active' => true,
            'draw_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'draw_at' => now()->subDay()->toDateTimeString(),
            ]));

        $response->assertSessionHasErrors('draw_at');
    }

    public function test_completed_draw_can_keep_a_past_draw_date_on_update(): void
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
        ]);

        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => 'Nome antigo',
            'winner_enrollment_id' => $enrollment->id,
            'is_active' => false,
            'draw_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'name' => 'Nome corrigido',
                'is_active' => false,
                'draw_at' => now()->subDay()->toDateTimeString(),
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'name' => 'Nome corrigido',
        ]);
    }

    public function test_admin_cannot_change_a_raffle_draw_edition(): void
    {
        $otherEdition = Edition::factory()->create([
            'year' => now()->year - 1,
        ]);

        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'edition_id' => $otherEdition->id,
            ]));

        $response->assertStatus(422);

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'edition_id' => $this->edition->id,
        ]);
    }

    public function test_admin_can_delete_a_raffle_draw_without_a_winner(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.raffleDraws.destroy', $draw));

        $response->assertRedirect();
        $this->assertDatabaseMissing('raffle_draws', ['id' => $draw->id]);
    }

    public function test_admin_cannot_delete_a_raffle_draw_with_a_winner(): void
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
        ]);

        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'winner_enrollment_id' => $enrollment->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.raffleDraws.destroy', $draw));

        $response->assertRedirect();
        $response->assertSessionHas('flash', fn (array $flash) => $flash['bannerStyle'] === 'danger');
        $this->assertDatabaseHas('raffle_draws', ['id' => $draw->id]);
    }

    public function test_admin_cannot_delete_a_raffle_draw_with_purchased_tickets(): void
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
        ]);

        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        RaffleTicket::factory()->create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.raffleDraws.destroy', $draw));

        $response->assertRedirect();
        $response->assertSessionHas('flash', fn (array $flash) => $flash['bannerStyle'] === 'danger');

        $this->assertDatabaseHas('raffle_draws', ['id' => $draw->id]);
        $this->assertDatabaseHas('raffle_tickets', [
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
        ]);
    }

    public function test_non_admin_cannot_access_raffle_management(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->actingAs($participant->user)
            ->get(route('admin.raffleDraws.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_raffle_management(): void
    {
        $response = $this->get(route('admin.raffleDraws.index'));

        $response->assertRedirect(route('login'));
    }
}
