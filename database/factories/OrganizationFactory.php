<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => 'https://yandex.ru/maps/org/test',
            'title' => 'Test organization',
            'rating' => 4.7,
            'ratings_count' => 100,
            'reviews_count' => 50,
            'status' => 'completed',
        ];
    }
}