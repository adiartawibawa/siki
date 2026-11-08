<?php

use App\Http\Requests\SaveRoleRequest;
use App\Livewire\Concerns\Notifies;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Roles')] class extends Component {
    use Notifies;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $level = '1';

    public array $permissions = [];

    /** Aturan bisnis ada di service; komponen ini hanya mengurus state & tampilan. */
    protected RoleService $roleService;

    /** Livewire menyuntikkan dependensi lewat boot() pada setiap request. */
    public function boot(RoleService $roleService): void
    {
        $this->roleService = $roleService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    #[Computed]
    public function roles()
    {
        return $this->roleService->list();
    }

    /** Hanya permission yang dipegang pengguna ini yang boleh dibagikan. */
    #[Computed]
    public function grantable(): array
    {
        return $this->roleService->grantableAbilities(Auth::user());
    }

    /** Level tertinggi yang boleh dibuat: selalu di bawah level pembuatnya. */
    #[Computed]
    public function maxLevel(): int
    {
        return $this->roleService->maxLevel(Auth::user());
    }

    public function create(): void
    {
        $this->authorize('create', Role::class);

        $this->resetForm();
        $this->level = (string) min(10, $this->maxLevel);
        $this->dispatch('modal-open-role-form');
    }

    public function edit(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        $this->resetForm();
        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->description = (string) $role->description;
        $this->level = (string) $role->level;
        $this->permissions = $role->permissions->pluck('name')->all();
        $this->dispatch('modal-open-role-form');
    }

    public function save(): void
    {
        $editing = $this->editingId ? Role::findOrFail($this->editingId) : null;

        $editing ? $this->authorize('update', $editing) : $this->authorize('create', Role::class);

        // Aturan validasi dipakai ulang dari FormRequest yang sama dengan API.
        $data = $this->validate(SaveRoleRequest::rulesFor(Auth::user(), $editing));

        $this->roleService->save(Auth::user(), $editing, $data);

        $this->notify('Role “' . trim($data['name']) . ($editing ? '” was updated.' : '” was created.'));
        $this->resetForm();
        $this->dispatch('modal-close-role-form');
    }

    public function remove(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);

        $this->roleService->delete($role);

        $this->notify("Role “{$role->name}” was deleted.");
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'description', 'level', 'permissions');
        $this->resetValidation();
    }
};
?>

@php
    $groups = collect(\App\Enums\Ability::cases())->groupBy(fn($a) => $a->group());
    $reference = collect(\App\Enums\UserRole::cases())->map(fn($r) => $r->value . ' ' . $r->level())->join(' · ');
@endphp

<div class="space-y-4">

    <x-ui.card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Roles</h2>
                <p class="text-sm text-slate-500">Higher level means more authority over other accounts.</p>
            </div>

            @can('create', \App\Models\Role::class)
                <x-ui.button variant="dark" icon="plus" wire:click="create">New role</x-ui.button>
            @endcan
        </div>

        <x-ui.table class="mt-4">
            <x-slot:head>
                <th scope="col">Role</th>
                <th scope="col">Level</th>
                <th scope="col">Users</th>
                <th scope="col">Permissions</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </x-slot:head>

            @foreach ($this->roles as $r)
                <tr wire:key="role-{{ $r->id }}">
                    <td>
                        <div class="flex items-center gap-2">
                            <x-ui.badge :tone="$r->tone()" :dot="false">{{ $r->name }}</x-ui.badge>
                            @if ($r->is_system)
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                    <x-ui.icon name="lock" size="xs" /> System
                                </span>
                            @endif
                        </div>
                        @if ($r->description)
                            <p class="mt-1 max-w-[46ch] text-xs text-slate-500">{{ $r->description }}</p>
                        @endif
                    </td>
                    <td class="tabular-nums">{{ $r->level }}</td>
                    <td class="tabular-nums">{{ $r->users_count }}</td>
                    <td class="tabular-nums">{{ $r->permissions_count }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            @can('update', $r)
                                <x-ui.button variant="ghost" size="icon-sm" wire:click="edit({{ $r->id }})"
                                    aria-label="Edit {{ $r->name }}">
                                    <x-ui.icon name="pencil" size="sm" />
                                </x-ui.button>
                            @endcan
                            @can('delete', $r)
                                <x-ui.button variant="ghost" size="icon-sm" class="text-red-600 hover:bg-red-50"
                                    wire:click="remove({{ $r->id }})"
                                    wire:confirm="Delete the role {{ $r->name }}?"
                                    aria-label="Delete {{ $r->name }}">
                                    <x-ui.icon name="trash" size="sm" />
                                </x-ui.button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>

    <x-ui.modal name="role-form" :title="$editingId ? 'Edit role' : 'New role'">
        <form wire:submit="save" class="space-y-4">
            <x-form.input label="Name" name="name" wire:model="name" autocomplete="off" required />
            <x-form.input label="Description" name="description" wire:model="description" :optional="true" />

            <div>
                <x-form.input label="Level (1 to {{ $this->maxLevel }})" name="level" type="number" min="1"
                    max="{{ $this->maxLevel }}" wire:model="level" required />
                <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                    Accounts can only be managed by someone with a higher level. For reference: {{ $reference }}.
                </p>
            </div>

            <fieldset>
                <legend class="mb-1.5 text-xs font-medium text-slate-600">Permissions</legend>
                <div class="max-h-56 space-y-4 overflow-y-auto rounded-xl border border-slate-200 p-3">
                    @foreach ($groups as $group => $abilities)
                        <div>
                            <p class="text-xs font-semibold text-slate-500">{{ $group }}</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($abilities as $a)
                                    @php $can = in_array($a->value, $this->grantable, true); @endphp
                                    <label
                                        class="{{ $can ? 'text-slate-800' : 'text-slate-400' }} flex items-center gap-2.5 text-sm">
                                        <input type="checkbox" value="{{ $a->value }}" wire:model="permissions"
                                            @disabled(!$can) class="size-4 rounded accent-brand-600">
                                        {{ $a->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Greyed-out permissions are ones you don’t hold yourself, so you
                    can’t hand them out.</p>
            </fieldset>

            <div class="flex justify-end gap-2 pt-1">
                <x-ui.button @click="$dispatch('modal-close-role-form')">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="dark" wire:loading.attr="disabled" wire:target="save">
                    {{ $editingId ? 'Save changes' : 'Create role' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
