<?php

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateProject
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __invoke(Project $project, array $input): Project
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($project, $input) {
            $project->update([
                'name' => $input['name'] ?? $project->name,
                'display_name' => $input['display_name'] ?? $project->display_name,
                'description' => $input['description'] ?? $project->description,
            ]);

            if (isset($input['assigned_users'])) {
                /** @var array<int|string> $users */
                $users = $input['assigned_users'];
                $project->assignedUsers()->sync($users);
            }

            if (isset($input['assigned_teams'])) {
                /** @var array<int|string> $teams */
                $teams = $input['assigned_teams'];
                $project->assignedTeams()->sync($teams);
            }

            return $project;
        });
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['sometimes', 'required', 'string', 'max:50'],
            'assigned_users' => ['sometimes', 'array'],
            'assigned_teams' => ['sometimes', 'array'],
        ];
    }
}
