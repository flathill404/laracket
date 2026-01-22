<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\TeamResource;
use App\Models\User;
use App\Queries\GetUserTeams;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class UserTeamController
{
    public function index(User $user, GetUserTeams $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $user);

        return TeamResource::collection($query($user));
    }
}
