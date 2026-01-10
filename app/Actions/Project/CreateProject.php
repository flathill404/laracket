<?php

namespace App\Actions\Project;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateProject
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(User $creator, Organization $organization, array $input): Project
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($organization, $input) {
            /** @var Project $project */
            $project = $organization->projects()->create([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'description' => $input['description'] ?? null,
            ]);

            if (isset($input['assigned_users'])) {
                $project->assignedUsers()->sync($input['assigned_users']);
            }

            if (isset($input['assigned_teams'])) {
                $project->assignedTeams()->sync($input['assigned_teams']);
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
            'name' => ['required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:50'],
        ];
    }
}
