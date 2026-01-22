<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Comment\CreateComment;
use App\Http\Resources\CommentResource;
use App\Models\Ticket;
use App\Queries\GetTicketComments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketCommentController
{
    public function index(Ticket $ticket, GetTicketComments $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        // Ensure user has access to the project
        Gate::authorize('view', $ticket->project);

        $comments = $query($ticket);

        return CommentResource::collection($comments);
    }

    public function store(Request $request, Ticket $ticket, CreateComment $action): JsonResponse
    {
        Gate::authorize('view', $ticket->project); // Assuming view access is enough to comment for now

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $data */
        $data = $request->all();
        $comment = $action($user, $ticket, $data);

        return response()->json(new CommentResource($comment), 201);
    }
}
