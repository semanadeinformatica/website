<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\FinalPrize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinalPrizeTest extends TestCase
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

    public function test_shop_page_shows_the_current_edition_final_prizes_in_order(): void
    {
        $otherEdition = Edition::factory()->create(['year' => now()->year - 1]);

        FinalPrize::factory()->for($this->edition)->create([
            'place' => 3,
            'title' => 'Terceiro prémio',
        ]);
        FinalPrize::factory()->for($this->edition)->create([
            'place' => 1,
            'title' => 'Primeiro prémio',
        ]);
        FinalPrize::factory()->for($this->edition)->create([
            'place' => 2,
            'title' => 'Segundo prémio',
        ]);
        FinalPrize::factory()->for($otherEdition)->create([
            'place' => 1,
            'title' => 'Prémio de outra edição',
        ]);

        $response = $this->get(route('shop.show'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Shop')
            ->has('finalPrizes', 3)
            ->where('finalPrizes.0.place', 1)
            ->where('finalPrizes.0.title', 'Primeiro prémio')
            ->where('finalPrizes.1.place', 2)
            ->where('finalPrizes.2.place', 3)
        );
    }

    public function test_admin_can_list_final_prizes(): void
    {
        $prize = FinalPrize::factory()->for($this->edition)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finalPrizes.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('CRUD/FinalPrizes/Index', false)
            ->has('items.data', 1)
            ->where('items.data.0.id', $prize->id)
            ->has('with.editions', 1)
        );
    }

    public function test_admin_can_create_a_final_prize(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.finalPrizes.store'), [
                'edition_id' => $this->edition->id,
                'place' => 2,
                'title' => 'Teclado mecânico',
            ]);

        $response->assertRedirect(route('admin.finalPrizes.index'));

        $this->assertDatabaseHas('final_prizes', [
            'edition_id' => $this->edition->id,
            'place' => 2,
            'title' => 'Teclado mecânico',
        ]);
    }

    public function test_admin_can_upload_a_final_prize_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.finalPrizes.store'), [
                'edition_id' => $this->edition->id,
                'place' => 1,
                'title' => 'Portátil',
                'image' => UploadedFile::fake()->image('prize.png'),
            ]);

        $response->assertRedirect(route('admin.finalPrizes.index'));

        $prize = FinalPrize::firstOrFail();

        $this->assertNotNull($prize->image_path);
        Storage::disk('public')->assertExists($prize->image_path);
        $this->assertNotNull($prize->final_prize_image_url);
    }

    public function test_admin_can_update_a_final_prize(): void
    {
        $prize = FinalPrize::factory()->for($this->edition)->create([
            'place' => 1,
            'title' => 'Título antigo',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.finalPrizes.update', $prize), [
                'edition_id' => $this->edition->id,
                'place' => 2,
                'title' => 'Título novo',
            ]);

        $response->assertRedirect(route('admin.finalPrizes.index'));

        $this->assertDatabaseHas('final_prizes', [
            'id' => $prize->id,
            'place' => 2,
            'title' => 'Título novo',
        ]);
    }

    public function test_a_place_can_only_be_used_once_per_edition(): void
    {
        FinalPrize::factory()->for($this->edition)->create(['place' => 1]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.finalPrizes.store'), [
                'edition_id' => $this->edition->id,
                'place' => 1,
                'title' => 'Outro prémio',
            ]);

        $response->assertSessionHasErrors('place');
    }

    public function test_the_same_place_can_be_used_in_different_editions(): void
    {
        $otherEdition = Edition::factory()->create(['year' => now()->year - 1]);
        FinalPrize::factory()->for($this->edition)->create(['place' => 1]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.finalPrizes.store'), [
                'edition_id' => $otherEdition->id,
                'place' => 1,
                'title' => 'Prémio da edição anterior',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('final_prizes', [
            'edition_id' => $otherEdition->id,
            'place' => 1,
        ]);
    }

    public function test_admin_can_delete_a_final_prize(): void
    {
        $prize = FinalPrize::factory()->for($this->edition)->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.finalPrizes.destroy', $prize));

        $response->assertRedirect();
        $this->assertDatabaseMissing('final_prizes', ['id' => $prize->id]);
    }
}
