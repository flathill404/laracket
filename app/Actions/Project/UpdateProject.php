<?php

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProject
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(Project $project, array $input): Project
    {
        $validated = Validator::make($input, $this->rules())->validate();

        DB::transaction(function () use ($project, $validated) {
            $attributes = Arr::except($validated, ['assigned_users', 'assigned_teams']);

            $project->update($attributes);

            if (isset($validated['assigned_users'])) {
                $project->assignedUsers()->sync($validated['assigned_users']);
            }

            if (isset($validated['assigned_teams'])) {
                $project->assignedTeams()->sync($validated['assigned_teams']);
            }
        });

        return $project;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assigned_users' => ['sometimes', 'array'],
            'assigned_teams' => ['sometimes', 'array'],
        ];
    }
}
