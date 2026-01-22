<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;

class GetProjectDetail
{
    public function __invoke(Project $project): Project
    {
        return $project;
    }
}
