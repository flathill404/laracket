<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateOrganization
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __invoke(User $creator, array $input): Organization
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($creator, $input) {
            /** @var Organization $organization */
            $organization = Organization::create([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'owner_user_id' => $creator->id,
            ]);

            // Add creator as admin member
            $organization->users()->attach($creator->id, ['role' => 'admin']);

            return $organization;
        });
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:100'],
        ];
    }
}
