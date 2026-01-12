<?php

namespace App\Actions\Team;

use App\Models\Team;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateTeam
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(Team $team, array $input): Team
    {
        $validated = Validator::make($input, $this->rules())->validate();

        DB::transaction(function () use ($team, $validated) {
            // リレーション用のキー(members)を除外して更新
            $attributes = Arr::except($validated, ['members']);

            if (! empty($attributes)) {
                $team->update($attributes);
            }

            if (isset($validated['members'])) {
                $team->users()->syncWithPivotValues($validated['members'], ['role' => 'member']);
            }
        });

        return $team;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['sometimes', 'required', 'string', 'max:50'],
            'members' => ['sometimes', 'array'],
        ];
    }
}
