<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\UpdateUserProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function update(Request $request, UpdateUserProfile $action)
    {
        $user = $action($request->user(), $request->all());

        return response()->json($user);
    }
}
