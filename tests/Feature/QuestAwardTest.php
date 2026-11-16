<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QuestAwardTest extends TestCase
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

    /**
     * @return array{0: Participant, 1: Enrollment}
     */
    private function enrolledParticipant(): array
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()
            ->for($this->edition)
            ->for($participant)
            ->create();

        return [$participant, $enrollment];
    }

    private function award(Participant $participant, Quest $quest)
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.questAwards.store'), [
                'participant_id' => $participant->id,
                'quest_id' => $quest->id,
            ]);
    }

    public function test_admin_can_view_the_manual_award_page(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();

        $completedQuest = Quest::factory()->for($this->edition)->create([
            'name' => 'Tarefa concluída',
            'category' => 'COMPANY',
        ]);
        $pendingQuest = Quest::factory()->for($this->edition)->create([
            'name' => 'Tarefa pendente',
            'category' => 'WORKSHOP',
        ]);
        $enrollment->quests()->attach($completedQuest);

        $response = $this->actingAs($this->admin)->get(
            route('admin.questAwards.index', ['participant' => $participant->id])
        );

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/QuestAward', false)
            ->where('edition.id', $this->edition->id)
            ->where('participant.id', $participant->id)
            ->where('participant.name', $participant->user->name)
            ->where('enrollment.id', $enrollment->id)
            ->has('quests', 2)
            ->where('quests.0.id', $completedQuest->id)
            ->where('quests.0.completed', true)
            ->where('quests.0.available', false)
            ->where('quests.1.id', $pendingQuest->id)
            ->where('quests.1.completed', false)
            ->where('quests.1.available', true)
        );
    }

    public function test_admin_can_view_the_page_without_a_selected_participant(): void
    {
        Quest::factory()->for($this->edition)->create();

        $response = $this->actingAs($this->admin)->get(
            route('admin.questAwards.index')
        );

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/QuestAward', false)
            ->where('participant', null)
            ->where('enrollment', null)
            ->where('quests', [])
        );
    }

    public function test_event_quests_are_unavailable_when_the_participant_is_not_enrolled_in_the_event(): void
    {
        [$participant] = $this->enrolledParticipant();

        $eventType = EventType::factory()->create(['name' => 'workshop']);
        $eventDay = EventDay::factory()->for($this->edition)->create();
        $event = Event::factory()
            ->for($eventDay, 'event_day')
            ->for($eventType, 'type')
            ->create(['name' => 'Introdução ao Laravel']);

        Quest::factory()->for($this->edition)->create([
            'name' => 'Tarefa do evento',
            'requirement_type' => Event::class,
            'requirement_id' => $event->id,
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.questAwards.index', ['participant' => $participant->id])
        );

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->has('quests', 1)
            ->where('quests.0.completed', false)
            ->where('quests.0.available', false)
            ->where(
                'quests.0.unavailable_reason',
                'O participante não está inscrito no evento "Introdução ao Laravel" (workshop).'
            )
        );
    }

    public function test_admin_can_manually_award_a_quest(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();
        $quest = Quest::factory()->for($this->edition)->create();

        $response = $this->award($participant, $quest);

        $response->assertRedirect(route('admin.questAwards.index', [
            'participant' => $participant->id,
        ]));
        $response->assertSessionHas('flash.bannerStyle', 'success');
        $this->assertTrue(
            $enrollment->quests()->whereKey($quest->id)->exists()
        );
    }

    public function test_manually_awarding_a_quest_unlocks_the_slot_and_awards_points(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();
        $quest = Quest::factory()->for($this->edition)->create();
        $slot = Slot::factory()->create([
            'total_quests' => 1,
            'points' => 25,
        ]);
        $slot->quests()->attach($quest);

        $this->assertSame(0, $enrollment->fresh()->points);

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'success');
        $this->assertTrue(
            $enrollment->quests()->whereKey($quest->id)->exists()
        );
        $this->assertSame(25, $enrollment->fresh()->points);
    }

    public function test_admin_can_manually_award_an_event_quest_when_the_participant_is_enrolled_in_the_event(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();

        $eventType = EventType::factory()->create(['name' => 'talk']);
        $eventDay = EventDay::factory()->for($this->edition)->create();
        $event = Event::factory()
            ->for($eventDay, 'event_day')
            ->for($eventType, 'type')
            ->create();
        $enrollment->events()->attach($event);

        $quest = Quest::factory()->for($this->edition)->create([
            'requirement_type' => Event::class,
            'requirement_id' => $event->id,
        ]);

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'success');
        $this->assertTrue(
            $enrollment->quests()->whereKey($quest->id)->exists()
        );
    }

    public function test_admin_cannot_manually_award_an_event_quest_without_the_event_enrollment(): void
    {
        [$participant] = $this->enrolledParticipant();

        $eventType = EventType::factory()->create(['name' => 'workshop']);
        $eventDay = EventDay::factory()->for($this->edition)->create();
        $event = Event::factory()
            ->for($eventDay, 'event_day')
            ->for($eventType, 'type')
            ->create(['name' => 'Introdução ao Laravel']);

        $quest = Quest::factory()->for($this->edition)->create([
            'requirement_type' => Event::class,
            'requirement_id' => $event->id,
        ]);

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'O participante não está inscrito no evento "Introdução ao Laravel" (workshop).'
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_admin_cannot_manually_award_the_same_quest_twice(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();
        $quest = Quest::factory()->for($this->edition)->create([
            'name' => 'Visitar a banca',
        ]);
        $enrollment->quests()->attach($quest);

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'O participante já realizou a tarefa "Visitar a banca".'
        );
        $this->assertDatabaseCount('enrollment_quest', 1);
    }

    public function test_admin_cannot_manually_award_a_quest_from_another_edition(): void
    {
        [$participant] = $this->enrolledParticipant();

        $otherEdition = Edition::factory()->create(['year' => $this->edition->year - 1]);
        $quest = Quest::factory()->for($otherEdition)->create();

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'A tarefa selecionada não pertence à edição atual.'
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_admin_cannot_manually_award_a_quest_to_an_unenrolled_participant(): void
    {
        $participant = Participant::factory()->create();
        $quest = Quest::factory()->for($this->edition)->create();

        $response = $this->award($participant, $quest);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            "O participante não se inscreveu no {$this->edition->name}."
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_non_admins_cannot_access_the_manual_award_page(): void
    {
        $participant = Participant::factory()->create();

        $this->actingAs($participant->user)
            ->get(route('admin.questAwards.index'))
            ->assertForbidden();

        $quest = Quest::factory()->for($this->edition)->create();
        Enrollment::factory()
            ->for($this->edition)
            ->for($participant)
            ->create();

        $this->actingAs($participant->user)
            ->post(route('admin.questAwards.store'), [
                'participant_id' => $participant->id,
                'quest_id' => $quest->id,
            ])
            ->assertForbidden();
    }
}
