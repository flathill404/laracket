<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
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
