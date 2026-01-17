<?php

namespace App\Observers;

use App\Models\Comment;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        if (auth()->check() && $comment->ticket) {
            $comment->ticket->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'commented',
                'payload' => ['body' => $comment->body], // Assuming 'body' is the content field
            ]);
        }
    }
}
