<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Organization;
use App\Services\YandexReviewParser;
use App\Jobs\ParseOrganizationJob;

class ParseOrganizationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_saves_reviews()
    {
        $organization = Organization::factory()->create();

        $parser = \Mockery::mock(YandexReviewParser::class);

        $parser->shouldReceive('parse')->once()->andReturn([
            'title' => 'Test',
            'rating' => 5,
            'ratings_count' => 100,
            'reviews_count' => 1,
            'reviews' => [
                [
                    'author' => 'Afam',
                    'text' => 'Hello',
                    'rating' => 5,
                    'date' => now()
                ]
            ]
        ]);

        app()->instance(YandexReviewParser::class, $parser);

        (new ParseOrganizationJob($organization))->handle($parser);

        $this->assertDatabaseHas(
            'reviews',
            [
                'organization_id' => $organization->id,
                'author' => 'Afam'
            ]
        );

        $organization->refresh();

        $this->assertEquals('completed', $organization->status);
    }

    public function test_job_marks_failed()
    {
        $organization = Organization::factory()->create();

        $parser = \Mockery::mock(YandexReviewParser::class);

        $parser->shouldReceive('parse')->once()->andThrow(new \Exception());

        app()->instance(YandexReviewParser::class, $parser);

        (new ParseOrganizationJob($organization))->handle($parser);

        $organization->refresh();

        $this->assertEquals('failed', $organization->status);
    }
}
