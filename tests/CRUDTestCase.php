<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

const AUTH_REDIRECT = '/login';

abstract class CRUDTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $controller;

    protected string $model;

    protected array $relatedModels = [];

    protected array $validCreateData = [];

    protected array $validUpdateData = [];

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();

        $basename = Str::replaceEnd('CRUDTest', '', class_basename($this));

        $this->model ??= "App\\Models\\{$basename}";
        $this->controller ??= "App\\Http\\Controllers\\{$basename}CRUDController";
    }

    public function test_index_screen_cannot_be_rendered(): void
    {
        $response = $this->get(action([$this->controller, 'index']));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_index_screen_cannot_be_rendered_if_not_admin(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(action([$this->controller, 'index']));

        $response->assertForbidden();
    }

    public function test_index_screen_can_be_rendered_if_admin(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(action([$this->controller, 'index']));

        $response->assertOk();
    }

    public function test_show_screen_cannot_be_rendered(): void
    {
        $item = $this->model::factory()->create();

        $response = $this->get(action([$this->controller, 'show'], $item));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_show_screen_cannot_be_rendered_if_not_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->user);

        $response = $this->get(action([$this->controller, 'show'], $item));

        $response->assertForbidden();
    }

    public function test_show_screen_can_be_rendered_if_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get(action([$this->controller, 'show'], $item));

        $response->assertOk();
    }

    public function test_create_screen_cannot_be_rendered(): void
    {
        $response = $this->get(action([$this->controller, 'create']));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_create_screen_cannot_be_rendered_if_not_admin(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(action([$this->controller, 'create']));

        $response->assertForbidden();
    }

    public function test_create_screen_can_be_rendered_if_admin(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(action([$this->controller, 'create']));

        $response->assertOk();
    }

    public function test_edit_screen_cannot_be_rendered(): void
    {
        $item = $this->model::factory()->create();

        $response = $this->get(action([$this->controller, 'edit'], $item));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_edit_screen_cannot_be_rendered_if_not_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->user);

        $response = $this->get(action([$this->controller, 'edit'], $item));

        $response->assertForbidden();
    }

    public function test_edit_screen_can_be_rendered_if_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get(action([$this->controller, 'edit'], $item));

        $response->assertOk();
    }

    public function test_store_action_cannot_be_executed(): void
    {
        $response = $this->post(action([$this->controller, 'store']));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_store_action_cannot_be_executed_if_not_admin(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(action([$this->controller, 'store']));

        $response->assertForbidden();
    }

    public function test_store_action_can_be_executed_if_admin(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(action([$this->controller, 'store']));

        $response->assertInvalid();
    }

    public function test_store_action_can_be_executed_if_admin_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        $modelCount = $this->model::count();

        foreach ($this->validCreateData as $data) {
            $response = $this->post(action([$this->controller, 'store']), $data);

            $response->assertValid();

            $this->assertEquals(++$modelCount, $this->model::count());
        }
    }

    public function test_update_action_cannot_be_executed(): void
    {
        $item = $this->model::factory()->create();

        $response = $this->put(action([$this->controller, 'update'], $item));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_update_action_cannot_be_executed_if_not_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->user);

        $response = $this->put(action([$this->controller, 'update'], $item));

        $response->assertForbidden();
    }

    public function test_update_action_can_be_executed_if_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->put(action([$this->controller, 'update'], $item));

        $response->assertInvalid();
    }

    public function test_update_action_can_be_executed_if_admin_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        foreach ($this->validUpdateData as $data) {
            $item = $this->model::factory()->create();

            $response = $this->put(action([$this->controller, 'update'], $item), $data);

            $response->assertValid();

            $this->assertNotEquals($item->toArray(), $this->model::find($item->id)->toArray());
        }
    }

    public function test_destroy_action_cannot_be_executed(): void
    {
        $item = $this->model::factory()->create();

        $response = $this->delete(action([$this->controller, 'destroy'], $item));

        $response->assertRedirect(AUTH_REDIRECT);
    }

    public function test_destroy_action_cannot_be_executed_if_not_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->user);

        $response = $this->delete(action([$this->controller, 'destroy'], $item));

        $response->assertForbidden();
    }

    public function test_destroy_action_can_be_executed_if_admin(): void
    {
        $item = $this->model::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->delete(action([$this->controller, 'destroy'], $item));

        $response->assertValid();

        $this->assertNull($this->model::find($item->id));
    }
}
