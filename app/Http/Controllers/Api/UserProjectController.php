<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Models\User;
use App\Queries\GetUserProjects;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class UserProjectController
{
    public function index(User $user, GetUserProjects $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $user);

        return ProjectResource::collection($query($user));
    }
}
