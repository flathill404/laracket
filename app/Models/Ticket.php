<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $project_id
 * @property string $title
 * @property string|null $description
 * @property TicketStatus $status
 * @property float $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignees
 * @property-read int|null $assignees_count
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $reviewers
 * @property-read int|null $reviewers_count
 *
 * @method static \Database\Factories\TicketFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    /**
     * @use HasFactory<\Database\Factories\TicketFactory>
     */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'deadline' => 'datetime',
        'status' => TicketStatus::class,
    ];

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_user')
            ->withPivot('type')
            ->wherePivot('type', TicketUserType::Assignee)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_user')
            ->withPivot('type')
            ->wherePivot('type', TicketUserType::Reviewer)
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Comment, $this>
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }
}
