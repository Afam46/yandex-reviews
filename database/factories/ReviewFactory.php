<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organization;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'author' => fake()->name(),
            'text' => fake()->sentence(),
            'rating' => fake()->numberBetween(1, 5),
            'review_date' => now(),
        ];
    }
}
