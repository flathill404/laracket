<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        /** @var array<string, string> $validated */
        $validated = Validator::make($input, $this->rules(), $this->messages())
            ->validateWithBag('updatePassword');

        DB::transaction(function () use ($user, $validated) {
            $user->forceFill([
                'password' => Hash::make((string) $validated['password']),
            ])->save();
        });
    }

    /**
     * Get the validation rules for updating the user's password.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * Get the validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ];
    }
}
