<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'ticket_user')
            ->wherePivot('role', 'assignee')
            ->withTimestamps();
    }

    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'ticket_user')
            ->wherePivot('role', 'reviewer')
            ->withTimestamps();
    }
}
