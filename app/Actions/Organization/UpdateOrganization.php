<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateOrganization
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function update(Organization $organization, array $input): Organization
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($organization, $input) {
            $organization->update([
                'name' => $input['name'] ?? $organization->name,
                'display_name' => $input['display_name'] ?? $organization->display_name,
            ]);

            return $organization;
        });
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }
}
