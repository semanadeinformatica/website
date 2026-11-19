<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopManagementTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['scout.driver' => 'null']);

        $this->edition = Edition::factory()->create([
            'year' => (string) now()->year,
        ]);
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function enrolledParticipant(int $points = 100): array
    {
        $participant = Participant::factory()->create();
        $user = $participant->user;
        $enrollment = Enrollment::factory()
            ->for($this->edition)
            ->for($participant)
            ->create(['points' => $points]);

        return [$user, $enrollment];
    }

    public function test_shop_page_is_available_when_the_shop_is_enabled(): void
    {
        $response = $this->get(route('shop.show'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Shop')
            ->where('shopEnabled', true)
        );
    }

    public function test_participants_cannot_see_the_shop_when_it_is_disabled(): void
    {
        [$user] = $this->enrolledParticipant();
        $this->edition->update(['shop_enabled' => false]);

        $response = $this->actingAs($user)->get(route('shop.show'));

        $response->assertRedirect(route('home'));
    }

    public function test_admins_can_still_view_the_shop_when_it_is_disabled(): void
    {
        $this->edition->update(['shop_enabled' => false]);

        $response = $this->actingAs($this->admin)->get(route('shop.show'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Shop')
            ->where('shopEnabled', false)
        );
    }

    public function test_participants_cannot_buy_products_when_the_shop_is_disabled(): void
    {
        [$user, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create([
            'price' => 30,
            'stock' => 5,
        ]);
        $this->edition->update(['shop_enabled' => false]);

        $response = $this->actingAs($user)->post(route('shop.product.buy', $product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('enrollment_product', [
            'enrollment_id' => $enrollment->id,
            'product_id' => $product->id,
        ]);
        $this->assertSame(100, $enrollment->fresh()->points);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_participants_can_buy_products_when_the_shop_is_enabled(): void
    {
        [$user, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create([
            'price' => 30,
            'stock' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('shop.product.buy', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollment_product', [
            'enrollment_id' => $enrollment->id,
            'product_id' => $product->id,
            'price' => 30,
        ]);
        $this->assertSame(70, $enrollment->fresh()->points);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_admin_can_toggle_the_shop_availability(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.shop.toggle'))
            ->assertRedirect();

        $this->assertFalse($this->edition->fresh()->shop_enabled);

        $this->actingAs($this->admin)
            ->post(route('admin.shop.toggle'))
            ->assertRedirect();

        $this->assertTrue($this->edition->fresh()->shop_enabled);
    }

    public function test_admin_can_list_purchases_for_an_edition(): void
    {
        [$user, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create([
            'price' => 30,
            'stock' => 5,
        ]);
        $enrollment->products()->attach($product, ['price' => 30]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.shop.purchases'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ShopPurchases')
            ->where('purchases.data.0.enrollment_id', $enrollment->id)
            ->where('purchases.data.0.product_id', $product->id)
            ->where('purchases.data.0.price', 30)
            ->where('purchases.data.0.participant_name', $user->name)
            ->where('summary.purchases', 1)
            ->where('summary.participants', 1)
        );
    }

    public function test_admin_can_refund_and_remove_a_purchase(): void
    {
        [, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create([
            'price' => 30,
            'stock' => 4,
        ]);
        $enrollment->products()->attach($product, ['price' => 30]);

        $this->assertSame(70, $enrollment->fresh()->points);

        $response = $this->actingAs($this->admin)->post(route('admin.shop.purchases.refund', [
            'product' => $product->id,
            'enrollment' => $enrollment->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('enrollment_product', [
            'enrollment_id' => $enrollment->id,
            'product_id' => $product->id,
        ]);
        $this->assertSame(100, $enrollment->fresh()->points);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_refund_uses_the_price_paid_at_purchase_time(): void
    {
        [, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create([
            'price' => 30,
            'stock' => 4,
        ]);
        $enrollment->products()->attach($product, ['price' => 30]);

        $product->update(['price' => 90]);

        $this->actingAs($this->admin)->post(route('admin.shop.purchases.refund', [
            'product' => $product->id,
            'enrollment' => $enrollment->id,
        ]));

        $this->assertSame(100, $enrollment->fresh()->points);
    }

    public function test_non_admin_cannot_manage_shop_purchases(): void
    {
        [$user, $enrollment] = $this->enrolledParticipant();
        $product = Product::factory()->for($this->edition)->create();
        $enrollment->products()->attach($product, ['price' => $product->price]);

        $this->actingAs($user)
            ->get(route('admin.shop.purchases'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.shop.purchases.refund', [
                'product' => $product->id,
                'enrollment' => $enrollment->id,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('enrollment_product', [
            'enrollment_id' => $enrollment->id,
            'product_id' => $product->id,
        ]);
    }
}
