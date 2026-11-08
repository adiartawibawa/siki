<?php

namespace App\Http\Requests;

use App\Enums\Ability;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role
            ? $this->user()->can('update', $role)
            : $this->user()->can('create', Role::class);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return self::rulesFor($this->user(), $role instanceof Role ? $role : null);

    }

    /**
     * Aturan validasi yang bergantung pada siapa pelakunya dan role mana yang diedit.
     *
     * @return array<string, mixed>
     */
    public static function rulesFor(User $actor, ?Role $editing = null): array
    {
        $maxLevel = app(RoleService::class)->maxLevel($actor);

        return [
            'name' => [
                'required', 'string', 'min:2', 'max:50',
                // Nama harus unik tanpa membedakan huruf besar/kecil.
                function ($attribute, $value, $fail) use ($editing) {
                    $taken = Role::query()
                        ->whereRaw('lower(name) = ?', [Str::lower($value)])
                        ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
                        ->exists();

                    if ($taken) {
                        $fail('This role name is already in use.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:160'],
            'level' => ['required', 'integer', 'between:1,'.$maxLevel],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(array_column(Ability::cases(), 'value'))],
        ];
    }
}
