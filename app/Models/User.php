<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * 個人として直接アサインされたプロジェクト
     * (チーム経由のアサインはこれには含まれんから注意せよ)
     */
    public function assignedProjects()
    {
        return $this->morphToMany(Project::class, 'assignable', 'project_assignments')
            ->withTimestamps();
    }

    public function assignedTickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user')
            ->wherePivot('role', 'assignee')
            ->withTimestamps();
    }

    public function reviewingTickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user')
            ->wherePivot('role', 'reviewer')
            ->withTimestamps();
    }
}
