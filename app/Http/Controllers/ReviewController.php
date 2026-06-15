<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

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
}
