<?php

use App\Actions\Api\RevokeApiTokens;
use App\Actions\Users\ChangeRole;
use App\Enums\Ability;
use App\Enums\UserRole;
use App\Livewire\Concerns\Notifies;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Users')] class extends Component
{
    use Notifies, WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $roleFilter = '';

    #[Url(except: 'active')]
    public string $status = 'active';

    public ?string $editingId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $username = '';

    public string $email = '';

    public string $role = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'status'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->visibleTo(Auth::user())
            ->with('roles')
            ->when($this->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($this->status === 'all', fn ($q) => $q->withTrashed())
            ->when($this->status === 'unverified', fn ($q) => $q->whereNull('email_verified_at'))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';

                $q->where(fn ($w) => $w->whereLike('first_name', $term)->orWhereLike('last_name', $term)->orWhereLike('username', $term)->orWhereLike('email', $term));
            })
            ->when($this->roleFilter !== '' && in_array($this->roleFilter, $this->allRoles, true), fn ($q) => $q->role($this->roleFilter))
            ->orderBy('first_name')
            ->orderBy('username')
            ->paginate(10);
    }

    /** Semua role, untuk dropdown filter. */
    #[Computed]
    public function allRoles(): array
    {
        return Role::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($q) => $q->where('name', '!=', UserRole::SuperAdmin->value))
            ->orderByDesc('level')
            ->pluck('name')
            ->all();
    }

    /** Role yang boleh diberikan oleh pengguna yang sedang login. */
    #[Computed]
    public function roleOptions(): array
    {
        $actor = Auth::user();

        return Role::query()
            ->when(! $actor->isSuperAdmin(), fn ($q) => $q->where('level', '<', $actor->level()))
            ->orderByDesc('level')
            ->pluck('name', 'name')
            ->all();
    }

    #[Computed]
    public function roleDescription(): ?string
    {
        return $this->role === '' ? null : Role::where('name', $this->role)->value('description');
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        $this->resetForm();

        // Default: Regular User jika boleh, selain itu role terendah yang tersedia.
        $this->role = array_key_exists(UserRole::RegularUser->value, $this->roleOptions) ? UserRole::RegularUser->value : (string) array_key_last($this->roleOptions);

        $this->dispatch('modal-open-user-form');
    }

    public function edit(string $id): void
    {
        $user = User::findOrFail($id)->visibleTo(Auth::user())->findOrFail($id);
        $this->authorize('update', $user);

        $this->resetForm();
        $this->editingId = $user->id;
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->primaryRole()?->name ?? '';

        $this->dispatch('modal-open-user-form');
    }

    public function save(): void
    {
        $editing = $this->editingId ? User::query()->visibleTo(Auth::user())->findOrFail($this->editingId) : null;

        $editing ? $this->authorize('update', $editing) : $this->authorize('create', User::class);

        $this->username = Str::lower(trim($this->username));
        $this->email = Str::lower(trim($this->email));

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'alpha_dash:ascii', Rule::unique(User::class)->ignore($editing?->id)],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(User::class)->ignore($editing?->id)],
            'role' => ['required', Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
        ]);

        $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();
        $roleChanged = ! $editing || ! $editing->primaryRole()?->is($role);

        if ($roleChanged) {
            $editing ? $this->authorize('assignRole', [$editing, $role]) : $this->authorize('grantRole', [User::class, $role]);
        }

        $attributes = Arr::only($data, ['first_name', 'last_name', 'username', 'email']);
        $attributes['last_name'] = $attributes['last_name'] ?: null;

        if ($editing) {
            DB::transaction(function () use ($editing, $attributes, $role, $roleChanged) {
                $editing->fill($attributes)->save();

                if ($roleChanged) {
                    app(ChangeRole::class)->handle(Auth::user(), $editing, $role);
                }

                if ($roleChanged && ! $editing->fresh()->can(Ability::ApiAccess->value)) {
                    $editing->tokens()->delete(); // kehilangan izin API = token tidak dibiarkan menunggu
                }
            });

            $this->notify("{$editing->name} was updated.");
        } else {
            $user = DB::transaction(function () use ($attributes, $role) {
                $user = new User($attributes);
                // Kata sandi acak: orang ini menetapkan sendiri lewat tautan di email.
                $user->password = Str::random(40);
                $user->email_verified_at = now();
                $user->save();
                $user->assignRole($role);

                return $user;
            });

            Password::sendResetLink(['email' => $user->email]);

            $this->notify("{$user->name} was added. An email with a link to set a password is on its way.");
        }

        $this->resetForm();
        $this->dispatch('modal-close-user-form');
    }

    public function deactivate(string $id): void
    {
        $user = User::query()->visibleTo(Auth::user())->findOrFail($id);
        $this->authorize('delete', $user);

        // Mencabut token "ingat saya"; sesi aktif gugur sendiri karena akun yang terhapus tidak lagi dimuat.
        $user->forceFill(['remember_token' => Str::random(60)])->save();
        $user->tokens()->delete();
        $user->delete();
        $this->notify("{$user->name} was deactivated and can no longer log in.");
    }

    public function restore(string $id): void
    {
        $user = User::onlyTrashed()->visibleTo(Auth::user())->findOrFail($id);
        $this->authorize('restore', $user);

        $user->restore();
        $this->notify("{$user->name} was restored.");
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'first_name', 'last_name', 'username', 'email', 'role');
        $this->resetValidation();
    }

    public function revokeTokens(string $id): void
    {
        $user = User::findOrFail($id);

        $count = app(RevokeApiTokens::class)->handle(Auth::user(), $user);

        $this->notify("{$count} API ".Str::plural('token', $count)." revoked for {$user->name}.");
    }
};
?>

@php
    $me = auth()->user();
    $field =
        'h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
@endphp

<div class="space-y-4">

    <x-ui.card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Users</h2>
                <p class="text-sm text-slate-500">{{ $this->users->total() }}
                    {{ Str::plural('account', $this->users->total()) }}</p>
            </div>

            @can('create', \App\Models\User::class)
                <x-ui.button variant="dark" icon="user-plus" wire:click="create">Add user</x-ui.button>
            @endcan
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-ui.search outline placeholder="Search name, username or email" wire:model.live.debounce.300ms="search"
                class="w-full sm:w-72" />

            <select wire:model.live="roleFilter" aria-label="Filter by role" class="{{ $field }}">
                <option value="">All roles</option>
                @foreach ($this->allRoles as $roleName)
                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                @endforeach
            </select>

            <select wire:model.live="status" aria-label="Filter by status" class="{{ $field }}">
                <option value="active">Active</option>
                <option value="unverified">Unverified</option>
                <option value="deleted">Deactivated</option>
                <option value="all">Everyone</option>
            </select>
        </div>

        <x-ui.table class="mt-4">
            <x-slot:head>
                <th scope="col">User</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Joined</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </x-slot:head>

            @forelse ($this->users as $u)
                <tr wire:key="user-{{ $u->id }}">
                    <td>
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$u->name" size="sm" />
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">
                                    {{ $u->name }}
                                    @if ($u->is($me))
                                        <span class="ml-1 text-xs font-normal text-slate-500">(you)</span>
                                    @endif
                                </p>
                                <p class="truncate text-xs text-slate-500">{{ '@' . $u->username }} ·
                                    {{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $userRole = $u->primaryRole(); @endphp
                        @if ($userRole)
                            <x-ui.badge :tone="$userRole->tone()" :dot="false">{{ $userRole->name }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">No role</span>
                        @endif
                    </td>
                    <td>
                        @if ($u->trashed())
                            <x-ui.badge tone="neutral">Deactivated</x-ui.badge>
                        @elseif (is_null($u->email_verified_at))
                            <x-ui.badge tone="warning">Unverified</x-ui.badge>
                        @else
                            <x-ui.badge tone="success">Active</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-slate-500">{{ $u->created_at?->translatedFormat('d M Y') }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            @if ($u->is($me))
                                <x-ui.button variant="ghost" size="icon-sm" :href="route('account.details')"
                                    aria-label="Edit your account">
                                    <x-ui.icon name="user" size="sm" />
                                </x-ui.button>
                                @if ($u->tokens_count > 0)
                                    @can('manageTokens', $u)
                                        <x-ui.button variant="ghost" size="icon-sm" class="text-amber-600 hover:bg-amber-50"
                                            wire:click="revokeTokens('{{ $u->id }}')"
                                            wire:confirm="Revoke {{ $u->tokens_count }} API {{ Str::plural('token', $u->tokens_count) }} for {{ $u->name }}?"
                                            aria-label="Revoke API tokens for {{ $u->name }}">
                                            <x-ui.icon name="key-round" size="sm" />
                                        </x-ui.button>
                                    @endcan
                                @endif
                            @elseif ($u->trashed())
                                @can('restore', $u)
                                    <x-ui.button variant="ghost" size="icon-sm" wire:click="restore('{{ $u->id }}')"
                                        wire:confirm="Restore {{ $u->name }}?"
                                        aria-label="Restore {{ $u->name }}">
                                        <x-ui.icon name="rotate-ccw" size="sm" />
                                    </x-ui.button>
                                @endcan
                            @else
                                @can('update', $u)
                                    <x-ui.button variant="ghost" size="icon-sm" wire:click="edit('{{ $u->id }}')"
                                        aria-label="Edit {{ $u->name }}">
                                        <x-ui.icon name="pencil" size="sm" />
                                    </x-ui.button>
                                @endcan
                                @can('delete', $u)
                                    <x-ui.button variant="ghost" size="icon-sm" class="text-red-600 hover:bg-red-50"
                                        wire:click="deactivate('{{ $u->id }}')"
                                        wire:confirm="Deactivate {{ $u->name }}? They won’t be able to log in until restored."
                                        aria-label="Deactivate {{ $u->name }}">
                                        <x-ui.icon name="user-round-x" size="sm" />
                                    </x-ui.button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                        No users match these filters.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-4">{{ $this->users->links() }}</div>
    </x-ui.card>

    <x-ui.modal name="user-form" :title="$editingId ? 'Edit user' : 'Add user'" :description="$editingId ? null : 'They’ll get an email with a link to choose their own password.'">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <x-form.input label="First name" name="first_name" wire:model="first_name" required />
                <x-form.input label="Last name" name="last_name" wire:model="last_name" :optional="true" />
            </div>

            <x-form.input label="Username" name="username" wire:model="username" autocomplete="off" required />
            <x-form.input label="Email address" name="email" type="email" wire:model="email" autocomplete="off"
                required />

            <div>
                <x-form.select label="Role" name="role" wire:model.live="role" :options="$this->roleOptions" />
                @if ($this->roleDescription)
                    <p class="mt-1.5 text-xs text-slate-500">{{ $this->roleDescription }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <x-ui.button @click="$dispatch('modal-close-user-form')">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="dark" wire:loading.attr="disabled" wire:target="save">
                    {{ $editingId ? 'Save changes' : 'Add user' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
