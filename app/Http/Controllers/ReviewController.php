<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Services\YandexReviewParser;

class ReviewController extends Controller
{
    public function index(Organization $organization)
    {
        abort_if($organization->user_id !== Auth::id(), 403);

        return $organization->reviews()->latest('review_date')->paginate(50);
    }

    public function parse(Request $request, YandexReviewParser $parser)
    {
        $reviews = $parser->parse($request->url);

        return response()->json($reviews);
    }
}
