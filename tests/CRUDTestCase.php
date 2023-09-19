<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class CRUDTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $controller;

    protected string $model;

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

        $response->assertRedirect();
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

        $response->assertRedirect();
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

        $response->assertRedirect();
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

        $response->assertRedirect();
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
}
