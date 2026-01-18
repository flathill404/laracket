<?php

namespace App\Models;

use App\Values\ActivityPayload;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $ticket_id
 * @property string $user_id
 * @property string $type
 * @property ActivityPayload|null $payload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketActivity whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
