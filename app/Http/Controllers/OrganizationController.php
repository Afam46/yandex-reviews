<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Services\YandexReviewParser;

class OrganizationController extends Controller
{
    public function store(Request $request)
    {   
        $validated = $request->validate([
            'url' => [
                'required',
                'url',
                'regex:/yandex/'
            ]
        ]);

        $organization = Organization::updateOrCreate(
            [
                'user_id' => Auth::id()
            ],
            [
                'url' => $validated['url']
            ]
        );

        $parser = app(YandexReviewParser::class);

        $parsed = $parser->parse(
            $validated['url']
        );

        $organization->update([
            'title' => $parsed['title'],
            'rating' => $parsed['rating'],
            'ratings_count' => $parsed['ratings_count'],
            'reviews_count' => $parsed['reviews_count'],
        ]);

        $organization->reviews()->delete();

        foreach ($parsed['reviews'] as $review) {
            $organization->reviews()->create([
                'author' => $review['author'],
                'text' => $review['text'],
                'rating' => $review['rating'],
                'review_date' => $review['date'],
            ]);
        }

        return $organization->load('reviews');
    }

    public function show()
    {
        return Organization::with('reviews')->where('user_id', Auth::id())->first();
    }
}
