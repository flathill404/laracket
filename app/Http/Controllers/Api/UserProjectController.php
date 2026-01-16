<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Queries\GetUserProjects;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserProjectController
{
    public function index(Request $request, \App\Models\User $user, GetUserProjects $query): AnonymousResourceCollection
    {
        if ($request->user()->id !== $user->id) {
            abort(403);
        }

        return ProjectResource::collection($query($user));
    }
}
