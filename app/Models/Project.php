<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTeams()
    {
        return $this->morphToMany(Team::class, 'assignable', 'project_assignments')
                    ->withTimestamps();
    }

    public function assignedUsers()
    {
        return $this->morphToMany(User::class, 'assignable', 'project_assignments')
                    ->withTimestamps();
    }
}