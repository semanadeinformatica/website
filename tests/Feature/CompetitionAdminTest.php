<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompetitionAdminTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['scout.driver' => 'null']);

        $this->edition = Edition::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $competition = Competition::factory()->for($this->edition)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.competitions.edit', $competition));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('CRUD/Competition/Index', false)
            ->where('modal', 'edit')
            ->where('item.id', $competition->id)
            ->has('with.editions', 1)
        );
    }

    public function test_admin_can_update_a_competition_without_changing_its_slug(): void
    {
        // Competitions with the same name exist in different editions, so
        // their slugs must be able to differ.
        Competition::factory()->for($this->edition)->create([
            'name' => 'Competição de CTFs',
            'slug' => 'competicao-de-ctfs',
        ]);
        $competition = Competition::factory()->for($this->edition)->create([
            'name' => 'Competição de CTFs',
            'slug' => 'ctfs',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.competitions.update', $competition), $this->payload($competition, [
                'description' => 'Descrição atualizada',
            ]));

        $response->assertRedirect(route('admin.competitions.index'));
        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'slug' => 'ctfs',
            'description' => 'Descrição atualizada',
        ]);
    }

    public function test_updating_to_a_slug_used_by_another_competition_shows_a_validation_error(): void
    {
        Competition::factory()->for($this->edition)->create(['slug' => 'competicao-de-ctfs']);
        $competition = Competition::factory()->for($this->edition)->create(['slug' => 'ctfs']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.competitions.update', $competition), $this->payload($competition, [
                'slug' => 'competicao-de-ctfs',
            ]));

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'slug' => 'ctfs',
        ]);
    }

    public function test_creating_a_competition_with_a_slug_used_by_another_competition_shows_a_validation_error(): void
    {
        Competition::factory()->for($this->edition)->create(['slug' => 'competicao-de-ctfs']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.competitions.store'), [
                'theme' => 'Tema',
                'date_start' => '2026-11-16T10:00',
                'date_end' => '2026-11-18T18:00',
                'edition_id' => $this->edition->id,
                'name' => 'Competição de CTFs',
                'slug' => 'competicao-de-ctfs',
                'description' => null,
                'registration_link' => 'https://example.com/register',
                'regulation' => 'Regulamento',
            ]);

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('competitions', 1);
    }

    public function test_competition_fields_cannot_exceed_the_database_column_limits(): void
    {
        $competition = Competition::factory()->for($this->edition)->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.competitions.update', $competition), $this->payload($competition, [
                'name' => str_repeat('a', 256),
                'slug' => str_repeat('a', 256),
                'registration_link' => 'https://example.com/'.str_repeat('a', 256),
            ]));

        $response->assertSessionHasErrors(['name', 'slug', 'registration_link']);
        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'name' => $competition->name,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Competition $competition, array $overrides = []): array
    {
        return array_merge([
            'theme' => $competition->theme,
            'date_start' => $competition->date_start->format('Y-m-d\TH:i'),
            'date_end' => $competition->date_end->format('Y-m-d\TH:i'),
            'edition_id' => $competition->edition_id,
            'name' => $competition->name,
            'slug' => $competition->slug,
            'description' => $competition->description,
            'registration_link' => $competition->registration_link,
            'regulation' => $competition->regulation,
        ], $overrides);
    }
}
