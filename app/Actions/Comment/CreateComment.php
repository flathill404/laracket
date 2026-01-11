<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateComment
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function __invoke(User $user, Ticket $ticket, array $data): Comment
    {
        Validator::make($data, [
            'content' => ['required', 'string', 'max:1000'],
        ])->validate();

        /** @var Comment $comment */
        $comment = $ticket->comments()->create([
            'user_id' => $user->id,
            'content' => $data['content'],
        ]);

        return $comment;
    }
}
