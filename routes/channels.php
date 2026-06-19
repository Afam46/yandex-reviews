<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum'],]);

Broadcast::channel('organizations', function ($user) {
    return $user !== null;
});