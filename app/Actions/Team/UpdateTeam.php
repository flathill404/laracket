<?php

namespace App\Actions\Team;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateTeam
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function update(Team $team, array $input): Team
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($team, $input) {
            $team->update([
                'name' => $input['name'] ?? $team->name,
                'display_name' => $input['display_name'] ?? $team->display_name,
            ]);

            if (isset($input['members'])) {
                $team->users()->syncWithPivotValues($input['members'], ['role' => 'member']);
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
            'name' => ['sometimes', 'required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }
}
