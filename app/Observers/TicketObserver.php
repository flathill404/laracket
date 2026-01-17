<?php

namespace App\Observers;

use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        if (auth()->check()) {
            $ticket->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'created',
                'payload' => null,
            ]);
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if (! auth()->check()) {
            return;
        }

        $dirty = $ticket->getDirty();
        // Ignore updated_at
        unset($dirty['updated_at']);

        if (empty($dirty)) {
            return;
        }

        $payload = [];
        foreach ($dirty as $key => $value) {
            $payload[$key] = [
                'from' => $ticket->getOriginal($key),
                'to' => $value,
            ];
        }

        $ticket->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'updated',
            'payload' => $payload,
        ]);
    }
}
