<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateOrganization
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(Organization $organization, array $input): Organization
    {
        /** @var array<string, mixed> $validated */
        $validated = Validator::make($input, $this->rules())->validate();

        DB::transaction(function () use ($organization, $validated) {
            $organization->update($validated);
        });

        return $organization;
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
