<?php

namespace App\Providers;

use App\Enums\Ability;
use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\MailSettings;
use Carbon\CarbonImmutable;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Super Admin lolos semua pengecekan permission (termasuk yang baru ditambahkan di kode).
        // Dibatasi pada nama permission, sehingga aturan di policy (mis. "super admin terakhir")
        // tetap berlaku dan tidak ikut dilewati.
        Gate::before(function (User $user, string $ability) {
            return in_array($ability, array_column(Ability::cases(), 'value'), true) && $user->isSuperAdmin()
                ? true
                : null;
        });

        $this->applySettings();

        Event::listen(Login::class, fn () => session()->forget('promo.dismissed'));

        // Di luar local, dokumentasi hanya untuk Super Admin. Tipe ?User wajib agar tamu tidak melempar galat.
        Gate::define('viewApiDocs', fn (?User $user) => $user?->isSuperAdmin() ?? false);

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        Scramble::configure()->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    private function applySettings(): void
    {
        if ($this->app->runningInConsole() && ! $this->settingsTableExists()) {
            return;
        }

        try {
            $general = app(GeneralSettings::class);
            $mail = app(MailSettings::class);
        } catch (QueryException|MissingSettings $e) {
            if (! $this->app->runningInConsole()) {
                report($e);
            }

            return;
        }

        config([
            'app.name' => $general->site_name,
            'mail.from.name' => $mail->from_name,
            'mail.from.address' => $mail->from_address,
        ]);

        app()->setLocale($general->locale);
    }

    private function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable(config('settings.repositories.database.table') ?? 'settings');
        } catch (\Throwable) {
            return false; // koneksi database belum tersedia
        }
    }
}
