<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProject
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(User $creator, Organization $organization, array $input): Project
    {
        $validated = Validator::make($input, $this->rules())->validate();

        $project = DB::transaction(function () use ($organization, $validated) {
            /** @var Project $project */
            $project = $organization->projects()->create([
                'slug' => $validated['slug'],
                'key' => $validated['key'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['assigned_users'])) {
                $project->assignedUsers()->sync((array) $validated['assigned_users']);
            }

            if (isset($validated['assigned_teams'])) {
                $project->assignedTeams()->sync((array) $validated['assigned_teams']);
            }

            return $project;
        });

        return $project;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:30', 'alpha_dash'],
            'key' => ['required', 'string', 'max:10', 'alpha', 'uppercase'],
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assigned_users' => ['sometimes', 'array'],
            'assigned_teams' => ['sometimes', 'array'],
        ];
    }
}
