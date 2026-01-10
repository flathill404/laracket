<?php

namespace App\Actions\Team;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateTeam
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(User $creator, Organization $organization, array $input): Team
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($organization, $input) {
            /** @var Team $team */
            $team = $organization->teams()->create([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
            ]);

            if (isset($input['members'])) {
                $team->users()->attach($input['members'], ['role' => 'member']);
            }

            return $team;
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
