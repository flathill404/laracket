<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasUuids;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * 個人として直接アサインされたプロジェクト
     * (チーム経由のアサインはこれには含まれんから注意せよ)
     *
     * @return MorphToMany<Project, $this>
     */
    public function assignedProjects(): MorphToMany
    {
        return $this->morphToMany(Project::class, 'assignable', 'project_assignments')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Ticket, $this>
     */
    public function assignedTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user')
            ->wherePivot('role', 'assignee')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Ticket, $this>
     */
    public function reviewingTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user')
            ->wherePivot('role', 'reviewer')
            ->withTimestamps();
    }
}
