<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ParseOrganizationJob;

class OrganizationController extends Controller
{
    public function store(Request $request)
    {   
        $validated = $request->validate([
            'url' => [
                'required',
                'string',
                'regex:/yandex/'
            ]
        ]);

        $organization = Organization::create([
            'user_id' => Auth::id(),
            'url' => $validated['url'],
        ]);

        ParseOrganizationJob::dispatch($organization);

        return response()->json([
            'message' => 'Parsing started',
            'organization' => $organization
        ]);
    }

    public function show()
    {
        return Organization::where('user_id', Auth::id())->latest()->get();
    }

    public function delete(int $id)
    {
        $organization = Organization::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $organization->delete();
    }
}
