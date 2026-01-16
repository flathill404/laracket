<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TeamResource;
use App\Models\User;
use App\Queries\GetUserTeams;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserTeamController
{
    public function index(Request $request, User $user, GetUserTeams $query): AnonymousResourceCollection
    {
        if ($request->user()->id !== $user->id) {
            abort(403);
        }

        return TeamResource::collection($query($user));
    }
}
