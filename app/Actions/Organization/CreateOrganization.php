<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateOrganization
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(User $creator, array $input): Organization
    {
        $validated = Validator::make($input, $this->rules())->validate();

        $organization = DB::transaction(function () use ($creator, $validated) {
            /** @var Organization $organization */
            $organization = Organization::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'owner_user_id' => $creator->id,
            ]);

            // Add creator as admin member
            $organization->users()->attach($creator->id, ['role' => 'admin']);

            return $organization;
        });

        return $organization;
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
