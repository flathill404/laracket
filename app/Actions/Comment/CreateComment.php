<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        $validated = Validator::make($data, $this->rules())->validate();

        $comment = DB::transaction(function () use ($user, $ticket, $validated) {
            return $ticket->comments()->create([
                'user_id' => $user->id,
                'content' => $validated['content'],
            ]);
        });

        return $comment;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}
