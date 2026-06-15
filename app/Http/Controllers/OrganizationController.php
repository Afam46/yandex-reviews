<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Services\YandexParserService;

class OrganizationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
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
                'url' => $data['url']
            ]
        );

        $parser = app(YandexParserService::class);

        $data = $parser->parse($data['url']);

        $organization->update([
            'name' => $data['name'],
            'rating' => $data['rating'],
            'ratings_count' => $data['ratings_count'],
            'reviews_count' => $data['reviews_count'],
        ]);

        $organization->reviews()->delete();

        foreach ($data['reviews'] as $review) {
            $organization->reviews()->create($review);
        }

        return $organization;
    }

    public function show()
    {
        return Organization::with('reviews')->where('user_id', Auth::id())->first();
    }
}
