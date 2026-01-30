<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TicketActivityType;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        if (! Auth::check()) {
            return;
        }

        $ticket->activities()->create([
            'user_id' => Auth::id(),
            'type' => TicketActivityType::Created,
            'payload' => null,
        ]);
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if (! Auth::check()) {
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
            'user_id' => Auth::id(),
            'type' => TicketActivityType::Updated,
            'payload' => $payload,
        ]);
    }
}
