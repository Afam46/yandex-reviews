<?php

namespace App\Services;

class YandexParserService
{
    public function parse(string $url): array
    {
        return [
            'name' => 'Тестовая организация',
            'rating' => 4.7,
            'ratings_count' => 228,
            'reviews_count' => 3,
            'reviews' => [
                [
                    'author' => 'Иван',
                    'rating' => 5,
                    'text' => 'Имба',
                    'review_date' => now(),
                ],
                [
                    'author' => 'Петр',
                    'rating' => 4,
                    'text' => 'Нормально',
                    'review_date' => now(),
                ],
                [
                    'author' => 'Анна',
                    'rating' => 5,
                    'text' => 'Очень понравилось',
                    'review_date' => now(),
                ],
            ]
        ];
    }
}