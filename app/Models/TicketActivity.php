<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketActivityType;
use App\Values\ActivityPayload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $ticket_id
 * @property string $user_id
 * @property TicketActivityType $type
 * @property ActivityPayload|null $payload
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
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
        'updated_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'type' => TicketActivityType::class,
        'payload' => ActivityPayload::class,
    ];

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
