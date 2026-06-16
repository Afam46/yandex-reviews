<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Services\YandexReviewParser;

class ReviewController extends Controller
{
    public function index()
    {
        $organization = Organization::where('user_id',Auth::id())->first();

        if (!$organization) {
            return response()->json([
                'data' => []
            ]);
        }

        return $organization->reviews()->latest('review_date')->paginate(50);
    }

    public function parse(Request $request, YandexReviewParser $parser) {
        $reviews = $parser->parse(
            $request->url
        );

        return response()->json(
            $reviews
        );
    }
}
