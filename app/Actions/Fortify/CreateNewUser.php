<?php

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        abort_unless(app(GeneralSettings::class)->registration_enabled, 403);

        $input['username'] = Str::lower(trim((string) ($input['username'] ?? '')));
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'alpha_dash:ascii', Rule::unique(User::class)],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::defaults()],
            'terms' => ['accepted'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'] ?: null,
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => $input['password'], // di-hash oleh cast
            ]);

            $user->assignRole(UserRole::RegularUser->value);

            return $user;
        });
    }
}
