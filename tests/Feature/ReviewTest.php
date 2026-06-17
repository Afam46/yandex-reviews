<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Review;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_reviews()
    {
        $user = User::factory()->create();

        $organization = Organization::factory()->for($user)->create();

        Review::factory()->count(10)->for($organization)->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson("/api/organizations/{$organization->id}/reviews");

        $response->assertOk();

        $response->assertJsonPath('total', 10);
    }

    public function test_user_cannot_get_foreign_reviews()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $organization = Organization::factory()->for($owner)->create();

        $this->actingAs($user, 'sanctum');

        $this->getJson("/api/organizations/{$organization->id}/reviews")->assertForbidden();
    }
}
