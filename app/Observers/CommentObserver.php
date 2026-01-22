<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;

use Illuminate\Support\Facades\Auth;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        if (Auth::check()) {
            $comment->ticket->activities()->create([
                'user_id' => Auth::id(),
                'type' => 'commented',
                'payload' => ['body' => $comment->body], // Assuming 'body' is the content field
            ]);
        }
    }
}
