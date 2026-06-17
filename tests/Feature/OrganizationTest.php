<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use App\Jobs\ParseOrganizationJob;
use App\Models\Organization;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_organization()
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/organization', [
            'url' => 'https://yandex.ru/maps/org/test'
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('organizations', [
            'user_id' => $user->id,
            'url' => 'https://yandex.ru/maps/org/test'
        ]);

        Queue::assertPushed(ParseOrganizationJob::class);
    }

    public function test_invalid_url_returns_validation_error()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/organization', [
            'url' => 'google.com'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_get_organizations()
    {
        $user = User::factory()->create();

        Organization::factory()->count(3)->for($user)->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/organization');

        $response->assertOk();

        $response->assertJsonCount(3);
    }

    public function test_user_can_delete_organization()
    {
        $user = User::factory()->create();

        $organization = Organization::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/organization/{$organization->id}/delete");

        $this->assertDatabaseMissing('organizations', [
            'id' => $organization->id
        ]);
    }

    public function test_user_cannot_delete_foreign_organization()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $organization = Organization::factory()->for($owner)->create();

        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/organization/{$organization->id}/delete")->assertNotFound();
    }
}
