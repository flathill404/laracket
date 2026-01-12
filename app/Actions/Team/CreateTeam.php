<?php

namespace App\Actions\Team;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateTeam
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(User $creator, Organization $organization, array $input): Team
    {
        $validated = Validator::make($input, $this->rules())->validate();

        $team = DB::transaction(function () use ($organization, $validated) {
            /** @var Team $team */
            $team = $organization->teams()->create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
            ]);

            if (isset($validated['members'])) {
                $team->users()->attach($validated['members'], ['role' => 'member']);
            }

            return $team;
        });

        return $team;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:50'],
            'members' => ['sometimes', 'array'],
        ];
    }
}
