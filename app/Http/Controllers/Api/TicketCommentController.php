<?php

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
    public function index(Ticket $ticket, GetTicketComments $query): JsonResponse
    {
        // Ensure user has access to the project
        Gate::authorize('view', $ticket->project);

        $comments = $query($ticket);

        return response()->json(CommentResource::collection($comments));
    }

    public function store(Request $request, Ticket $ticket, CreateComment $action): JsonResponse
    {
        Gate::authorize('view', $ticket->project); // Assuming view access is enough to comment for now

        /** @var \App\Models\User $user */
        $user = $request->user();
        $comment = $action($user, $ticket, $request->all());

        return response()->json(new CommentResource($comment), 201);
    }
}
