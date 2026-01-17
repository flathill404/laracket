<?php

namespace App\Models;

use App\Values\ActivityPayload;
use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => ActivityPayload::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Ticket, $this>
     */
    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    //
}
