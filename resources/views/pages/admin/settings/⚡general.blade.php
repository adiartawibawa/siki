<?php

use App\Settings\GeneralSettings;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('General settings')] class extends Component {
    public string $site_name = '';

    public string $site_description = '';

    public string $contact_email = '';

    public string $timezone = 'UTC';

    public string $locale = 'en';

    public bool $registration_enabled = false;

    public function mount(GeneralSettings $settings): void
    {
        // $this->authorize('manage-settings');

        $this->site_name = $settings->site_name;
        $this->site_description = (string) $settings->site_description;
        $this->contact_email = $settings->contact_email;
        $this->timezone = $settings->timezone;
        $this->locale = $settings->locale;
        $this->registration_enabled = $settings->registration_enabled;
    }

    #[Computed]
    public function timezones(): array
    {
        return collect(\DateTimeZone::listIdentifiers())->mapWithKeys(fn($t) => [$t => $t])->all();
    }

    #[Computed]
    public function locales(): array
    {
        // Hanya bahasa yang file terjemahannya ada di lang/.
        return ['en' => 'English', 'id' => 'Bahasa Indonesia'];
    }

    public function save(GeneralSettings $settings): void
    {
        // Aksi Livewire adalah request tersendiri: otorisasi wajib diulang di sini.
        // $this->authorize('manage-settings');

        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'site_description' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['required', 'email', 'max:255'],
            'timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'locale' => ['required', Rule::in(array_keys($this->locales))],
            'registration_enabled' => ['boolean'],
        ]);

        $settings->site_name = $data['site_name'];
        $settings->site_description = $data['site_description'] ?: null;
        $settings->contact_email = $data['contact_email'];
        $settings->timezone = $data['timezone'];
        $settings->locale = $data['locale'];
        $settings->registration_enabled = $data['registration_enabled'];
        $settings->save();

        $this->dispatch('settings-saved');
    }
};
?>

<div>
    <form wire:submit="save">
        <x-ui.card :padded="false" class="divide-y divide-slate-100 overflow-hidden">
            <x-form.form-section title="Identity"
                description="How the application names itself in the interface and in emails." class="p-6 sm:p-8">
                <div class="grid max-w-md gap-4">
                    <x-form.input label="Application name" name="site_name" wire:model="site_name" required />
                    <x-form.input label="Description" name="site_description" wire:model="site_description"
                        :optional="true" />
                    <x-form.input label="Contact email" name="contact_email" type="email" wire:model="contact_email"
                        required />
                </div>
            </x-form.form-section>

            <x-form.form-section title="Regional" description="Language and the timezone used to show dates and times."
                class="p-6 sm:p-8">
                <div class="grid max-w-md gap-4">
                    <x-form.select label="Language" name="locale" wire:model="locale" :options="$this->locales" />
                    <x-form.select label="Display timezone" name="timezone" wire:model="timezone" :options="$this->timezones" />
                </div>
            </x-form.form-section>

            <x-form.form-section title="Access" description="Who can create an account." class="p-6 sm:p-8">
                <x-form.toggle label="Open registration" wire:model="registration_enabled"
                    description="When off, the sign up page is unavailable and new accounts must be created by an administrator." />
            </x-form.form-section>

            <x-admin.save-bar>Save changes</x-admin.save-bar>
        </x-ui.card>
    </form>
</div>
