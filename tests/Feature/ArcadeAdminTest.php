<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArcadeAdminTest extends TestCase
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
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
            'is_active' => true,
            'is_pvp' => true,
            'min_wager' => 10,
            'max_wager' => 500,
            'daily_win_cap' => 1000,
            'payout_config' => '{"house_edge_pct":10}',
        ], $overrides);
    }

    public function test_admin_can_list_arcade_games(): void
    {
        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.arcadeGames.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('CRUD/ArcadeGames/Index', false)
            ->has('items.data', 1)
            ->where('items.data.0.id', $game->id)
            ->where('items.data.0.slug', 'coin-flip')
            ->where('items.data.0.name', 'Coin Flip')
            ->has('with.editions', 1)
        );
    }

    public function test_admin_can_create_an_arcade_game(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload());

        $response->assertRedirect(route('admin.arcadeGames.index'));

        $game = ArcadeGame::query()
            ->where('slug', 'coin-flip')
            ->firstOrFail();

        $this->assertSame('Coin Flip', $game->name);
        $this->assertTrue($game->is_active);
        $this->assertTrue($game->is_pvp);
        $this->assertSame(10, $game->min_wager);
        $this->assertSame(500, $game->max_wager);
        $this->assertSame(1000, $game->daily_win_cap);
        $this->assertSame(['house_edge_pct' => 10], $game->payout_config);
    }

    public function test_admin_cannot_create_duplicate_slug_in_same_edition(): void
    {
        ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload());

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_create_same_slug_in_another_edition(): void
    {
        $otherEdition = Edition::factory()->create([
            'year' => now()->year - 1,
        ]);

        ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload([
                'edition_id' => $otherEdition->id,
            ]));

        $response->assertRedirect(route('admin.arcadeGames.index'));
    }

    public function test_admin_cannot_create_invalid_payout_config(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload([
                'payout_config' => '{invalid',
            ]));

        $response->assertSessionHasErrors('payout_config');
        $this->assertDatabaseCount('arcade_games', 0);
    }

    public function test_admin_cannot_create_scalar_payout_config(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload([
                'payout_config' => '10',
            ]));

        $response->assertSessionHasErrors('payout_config');
        $this->assertDatabaseCount('arcade_games', 0);
    }

    public function test_admin_cannot_create_null_payout_config(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload([
                'payout_config' => 'null',
            ]));

        $response->assertSessionHasErrors('payout_config');
        $this->assertDatabaseCount('arcade_games', 0);
    }

    public function test_admin_cannot_create_invalid_wager_bounds(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.arcadeGames.store'), $this->validPayload([
                'min_wager' => 100,
                'max_wager' => 50,
            ]));

        $response->assertSessionHasErrors('max_wager');
        $this->assertDatabaseCount('arcade_games', 0);
    }

    public function test_admin_can_update_an_arcade_game(): void
    {
        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'name' => 'Nome antigo',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.arcadeGames.update', $game), $this->validPayload([
                'name' => 'Coin Flip Actualizado',
                'is_active' => false,
                'is_pvp' => false,
            ]));

        $response->assertRedirect(route('admin.arcadeGames.index'));

        $game->refresh();

        $this->assertSame('Coin Flip Actualizado', $game->name);
        $this->assertFalse($game->is_active);
        $this->assertFalse($game->is_pvp);
        $this->assertSame(['house_edge_pct' => 10], $game->payout_config);
    }

    public function test_admin_cannot_update_slug_to_clash_with_another_game(): void
    {
        $otherGame = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'high-low',
        ]);

        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.arcadeGames.update', $game), $this->validPayload([
                'slug' => 'high-low',
            ]));

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseHas('arcade_games', [
            'id' => $otherGame->id,
            'slug' => 'high-low',
        ]);
    }

    public function test_admin_can_keep_same_slug_when_updating(): void
    {
        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.arcadeGames.update', $game), $this->validPayload([
                'name' => 'Coin Flip Actualizado',
            ]));

        $response->assertRedirect(route('admin.arcadeGames.index'));

        $this->assertDatabaseHas('arcade_games', [
            'id' => $game->id,
            'name' => 'Coin Flip Actualizado',
        ]);
    }

    public function test_admin_cannot_update_scalar_payout_config(): void
    {
        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.arcadeGames.update', $game), $this->validPayload([
                'payout_config' => '10',
            ]));

        $response->assertSessionHasErrors('payout_config');
        $this->assertDatabaseHas('arcade_games', [
            'id' => $game->id,
            'slug' => $game->slug,
        ]);
    }

    public function test_admin_can_delete_an_arcade_game(): void
    {
        $game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.arcadeGames.destroy', $game));

        $response->assertRedirect();
        $this->assertDatabaseMissing('arcade_games', ['id' => $game->id]);
    }

    public function test_non_admin_cannot_access_arcade_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.arcadeGames.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_arcade_management(): void
    {
        $response = $this->get(route('admin.arcadeGames.index'));

        $response->assertRedirect(route('login'));
    }
}
