<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestControllerTest extends TestCase
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

    private function participantWithCode(string $code = 'abc123def4'): Participant
    {
        $participant = Participant::factory()->create();
        $participant->forceFill(['quest_code' => $code])->save();

        return $participant;
    }

    private function enrolledParticipant(string $code = 'abc123def4'): array
    {
        $participant = $this->participantWithCode($code);
        $enrollment = Enrollment::factory()
            ->for($this->edition)
            ->for($participant)
            ->create();

        return [$participant, $enrollment];
    }

    private function give(Quest $quest, string $code)
    {
        return $this->actingAs($this->admin)
            ->post(route('quest.give', ['quest' => $quest->id]), [
                'quest_code' => $code,
            ]);
    }

    public function test_unknown_code_returns_a_descriptive_validation_error(): void
    {
        $quest = Quest::factory()->for($this->edition)->create();

        $response = $this->give($quest, 'doesnotexist');

        $response->assertSessionHasErrors([
            'quest_code' => 'Código inválido ou expirado. Pede ao participante para gerar um novo QR code.',
        ]);
    }

    public function test_participant_not_enrolled_in_the_edition_gets_a_descriptive_error(): void
    {
        $participant = $this->participantWithCode();
        Enrollment::factory()->create();

        $quest = Quest::factory()->for($this->edition)->create();

        $response = $this->give($quest, $participant->quest_code);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            "O participante não se inscreveu no {$this->edition->name}."
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_participant_not_enrolled_in_the_required_event_gets_a_descriptive_error(): void
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

        $response = $this->give($quest, $participant->quest_code);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'O participante não está inscrito no evento "Introdução ao Laravel" (workshop).'
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_quest_already_given_gets_a_descriptive_error(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();

        $quest = Quest::factory()->for($this->edition)->create([
            'name' => 'Visitar a banca',
        ]);
        $enrollment->quests()->attach($quest);

        $response = $this->give($quest, $participant->quest_code);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'O participante já realizou a tarefa "Visitar a banca".'
        );
        $this->assertDatabaseCount('enrollment_quest', 1);
    }

    public function test_user_without_permission_gets_a_descriptive_error(): void
    {
        [$participant] = $this->enrolledParticipant();
        $quest = Quest::factory()->for($this->edition)->create();

        $otherParticipant = User::factory()->create();

        $response = $this->actingAs($otherParticipant)
            ->post(route('quest.give', ['quest' => $quest->id]), [
                'quest_code' => $participant->quest_code,
            ]);

        $response->assertSessionHas('flash.bannerStyle', 'danger');
        $response->assertSessionHas(
            'flash.banner',
            'Não tens permissão para atribuir esta tarefa a este participante.'
        );
        $this->assertDatabaseCount('enrollment_quest', 0);
    }

    public function test_admin_can_give_a_quest_to_an_enrolled_participant(): void
    {
        [$participant, $enrollment] = $this->enrolledParticipant();
        $quest = Quest::factory()->for($this->edition)->create();

        $response = $this->give($quest, $participant->quest_code);

        $response->assertSessionHas('flash.bannerStyle', 'success');
        $response->assertSessionHas('flash.banner', 'Tarefa atribuída com sucesso!');
        $this->assertTrue($enrollment->quests()->whereKey($quest->id)->exists());
    }

    public function test_admin_can_give_an_event_quest_to_an_enrolled_participant(): void
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

        $response = $this->give($quest, $participant->quest_code);

        $response->assertSessionHas('flash.bannerStyle', 'success');
        $this->assertTrue($enrollment->quests()->whereKey($quest->id)->exists());
    }
}
