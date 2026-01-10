<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Queries\GetUserProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request, GetUserProfile $query): \App\Models\User
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return $query($user);
    }
}
