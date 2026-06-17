<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Services\YandexReviewParser;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function index(Request $request, Organization $organization) {

        abort_if($organization->user_id !== Auth::id(), 403);

        $page = $request->integer('page', 1);

        return Cache::remember("organization:{$organization->id}.reviews.{$page}", now()->addMinutes(10),
            function () use ($organization, $page)
            {
                return $organization->reviews()->latest('review_date')->paginate(
                        perPage: 50,
                        page: $page
                    ) ->toArray();
            }
        );
    }

    public function parse(Request $request, YandexReviewParser $parser)
    {
        $reviews = $parser->parse($request->url);

        return response()->json($reviews);
    }
}
