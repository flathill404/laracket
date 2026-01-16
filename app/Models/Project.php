<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $assignedTeams
 * @property-read int|null $assigned_teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedUsers
 * @property-read int|null $assigned_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 *
 * @method static \Database\Factories\ProjectFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Project extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ProjectFactory>
     */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function assignedTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_team')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->assignedUsers();
    }

    /**
     * Scope the query to projects visible to the given user.
     */
    /**
     * Scope the query to projects visible to the given user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Project>  $query
     */
    public function scopeVisibleToUser(Builder $query, User $user): void
    {
        $query->where(function (Builder $q) use ($user) {
            $q->whereOrganizationAccessibleByUser($user)
                ->orWhere(fn (Builder $q) => $q->whereDirectlyAssignedToUser($user))
                ->orWhere(fn (Builder $q) => $q->whereAssignedToUserViaTeam($user));
        });
    }

    /**
     * Scope the query to projects that are in an organization accessible to the user (Owner or Admin).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Project>  $query
     */
    public function scopeWhereOrganizationAccessibleByUser(Builder $query, User $user): void
    {
        $query->whereHas('organization', function (Builder $q) use ($user) {
            $q->where('owner_user_id', $user->id)
                ->orWhereHas('users', function (Builder $mq) use ($user) {
                    $mq->where('user_id', $user->id)
                        ->where('role', OrganizationRole::Admin);
                });
        });
    }

    /**
     * Scope the query to projects directly assigned to the user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Project>  $query
     */
    public function scopeWhereDirectlyAssignedToUser(Builder $query, User $user): void
    {
        $query->whereHas('assignedUsers', function (Builder $q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }

    /**
     * Scope the query to projects assigned to the user via a team.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Project>  $query
     */
    public function scopeWhereAssignedToUserViaTeam(Builder $query, User $user): void
    {
        $query->whereHas('assignedTeams.members', function (Builder $q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }
}
