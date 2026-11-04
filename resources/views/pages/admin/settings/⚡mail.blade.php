<?php

use App\Settings\MailSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Mail settings')] class extends Component {
    public string $from_name = '';

    public string $from_address = '';

    public string $reply_to = '';

    public ?string $testResult = null;

    public function mount(MailSettings $settings): void
    {
        // $this->authorize('manage-settings');

        $this->from_name = $settings->from_name;
        $this->from_address = $settings->from_address;
        $this->reply_to = (string) $settings->reply_to;
    }

    public function save(MailSettings $settings): void
    {
        // $this->authorize('manage-settings');

        $data = $this->validate([
            'from_name' => ['required', 'string', 'max:80'],
            'from_address' => ['required', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
        ]);

        $settings->from_name = $data['from_name'];
        $settings->from_address = $data['from_address'];
        $settings->reply_to = $data['reply_to'] ?: null;
        $settings->save();

        $this->testResult = null;
        $this->dispatch('settings-saved');
    }

    public function sendTest(): void
    {
        $this->authorize('manage-settings');

        try {
            Mail::raw('This is a test email from ' . config('app.name') . '.', fn($m) => $m->to(Auth::user()->email)->subject('Test email'));

            $this->testResult = 'sent';
        } catch (\Throwable $e) {
            report($e);
            $this->testResult = 'failed';
        }
    }
};
?>

<div class="space-y-4">
    <form wire:submit="save">
        <x-ui.card :padded="false" class="divide-y divide-slate-100 overflow-hidden">
            <x-form.form-section title="Sender"
                description="The name and address recipients see on emails from the application." class="p-6 sm:p-8">
                <div class="grid max-w-md gap-4">
                    <x-form.input label="From name" name="from_name" wire:model="from_name" required />
                    <x-form.input label="From address" name="from_address" type="email" wire:model="from_address"
                        required />
                    <x-form.input label="Reply-to address" name="reply_to" type="email" wire:model="reply_to"
                        :optional="true" />
                </div>
            </x-form.form-section>

            <x-admin.save-bar>Save changes</x-admin.save-bar>
        </x-ui.card>
    </form>

    <x-ui.card :padded="false" class="overflow-hidden">
        <x-form.form-section title="Send a test email"
            description="Sends a message to your own address using the saved sender. The server, port and credentials come from the .env file."
            class="p-6 sm:p-8">
            @if ($testResult === 'sent')
                <x-ui.badge tone="success">Sent to {{ auth()->user()->email }}</x-ui.badge>
            @elseif ($testResult === 'failed')
                <x-ui.badge tone="danger">Couldn’t send. Check the MAIL_* values in .env and the log.</x-ui.badge>
            @else
                <p class="text-sm text-slate-600">Save your changes first, then send a test.</p>
            @endif

            <x-slot:actions>
                <x-ui.button wire:click="sendTest" wire:loading.attr="disabled" wire:target="sendTest">
                    Send test email
                </x-ui.button>
            </x-slot:actions>
        </x-form.form-section>
    </x-ui.card>
</div>
