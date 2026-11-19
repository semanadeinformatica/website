<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\RaffleDraw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaffleDrawAdminTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    private Event $closingEvent;

    private Quest $closingQuest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create(['year' => 2026]);
        $this->admin = User::factory()->admin()->create();

        $eventDay = EventDay::factory()->create(['edition_id' => $this->edition->id]);
        $eventType = EventType::factory()->create(['name' => 'talk']);

        $this->closingEvent = Event::factory()->create([
            'event_day_id' => $eventDay->id,
            'event_type_id' => $eventType->id,
            'name' => 'Sessão de Encerramento e Entrega de Prémios',
            'topic' => 'Encerramento',
        ]);

        $this->closingQuest = Quest::create([
            'name' => 'Assistir à Sessão de Encerramento',
            'edition_id' => $this->edition->id,
            'category' => 'TALK',
            'requirement_id' => $this->closingEvent->id,
            'requirement_type' => Event::class,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'edition_id' => $this->edition->id,
            'place' => 1,
            'required_quest_id' => $this->closingQuest->id,
            'name' => 'Sorteio de teste',
            'description' => 'Descrição do sorteio',
            'is_active' => true,
            'draw_at' => now()->addDay()->toDateTimeString(),
        ], $overrides);
    }

    private function makeDraw(int $place = 1, array $overrides = []): RaffleDraw
    {
        return RaffleDraw::factory()->create(array_merge([
            'edition_id' => $this->edition->id,
            'place' => $place,
            'required_quest_id' => $this->closingQuest->id,
            'name' => "{$place}º Prémio",
        ], $overrides));
    }

    public function test_admin_can_list_raffle_draws(): void
    {
        $draw = $this->makeDraw(place: 1, overrides: ['name' => 'Teclado Mecânico']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CRUD/RaffleDraws/Index', false)
            ->has('items.data', 1)
            ->where('items.data.0.id', $draw->id)
            ->where('items.data.0.name', 'Teclado Mecânico')
            ->where('items.data.0.place', 1)
            ->where('items.data.0.required_quest.id', $this->closingQuest->id)
            ->has('with.editions', 1)
            ->has('with.quests', 1)
        );
    }

    public function test_admin_index_loads_the_winner_relation(): void
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
        ]);

        $this->makeDraw(place: 1, overrides: [
            'winner_enrollment_id' => $enrollment->id,
            'is_active' => false,
            'draw_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
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
            'place' => 1,
            'required_quest_id' => $this->closingQuest->id,
            'name' => 'Sorteio de teste',
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
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['place' => 0]))
            ->assertSessionHasErrors('place');

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['place' => 4]))
            ->assertSessionHasErrors('place');

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['required_quest_id' => 99999]))
            ->assertSessionHasErrors('required_quest_id');

        $this->makeDraw(place: 1);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $this->validPayload(['place' => 1]))
            ->assertSessionHasErrors('place');
    }

    public function test_admin_can_update_an_active_raffle_draw(): void
    {
        $draw = $this->makeDraw(place: 1, overrides: [
            'name' => 'Nome antigo',
            'is_active' => true,
            'draw_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'place' => 2,
                'name' => 'Nome novo',
            ]));

        $response->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'name' => 'Nome novo',
            'place' => 2,
            'required_quest_id' => $this->closingQuest->id,
        ]);
    }

    public function test_active_draw_can_keep_a_past_draw_date_on_update(): void
    {
        $draw = $this->makeDraw(place: 1, overrides: [
            'is_active' => true,
            'draw_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.raffleDraws.update', $draw), $this->validPayload([
                'draw_at' => now()->subDay()->toDateTimeString(),
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'id' => $draw->id,
            'place' => 1,
        ]);
    }

    public function test_admin_cannot_change_a_raffle_draw_edition(): void
    {
        $otherEdition = Edition::factory()->create([
            'year' => now()->year - 1,
        ]);

        $draw = $this->makeDraw(place: 1);

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
        $draw = $this->makeDraw(place: 1);

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

        $draw = $this->makeDraw(place: 1, overrides: [
            'winner_enrollment_id' => $enrollment->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.raffleDraws.destroy', $draw));

        $response->assertRedirect();
        $response->assertSessionHas('flash', fn (array $flash) => $flash['bannerStyle'] === 'danger');
        $this->assertDatabaseHas('raffle_draws', ['id' => $draw->id]);
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
