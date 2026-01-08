<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
     * @return MorphToMany<Team, $this>
     */
    public function assignedTeams(): MorphToMany
    {
        return $this->morphedByMany(Team::class, 'assignable', 'project_assignments')
            ->withTimestamps();
    }

    /**
     * @return MorphToMany<User, $this>
     */
    public function assignedUsers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'assignable', 'project_assignments')
            ->withTimestamps();
    }
}
