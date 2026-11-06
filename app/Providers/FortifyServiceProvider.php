<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\QuietFailedPasswordResetLinkRequestResponse;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Email tidak dikenal dan permintaan yang tertahan dijawab sama dengan yang berhasil.
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, QuietFailedPasswordResetLinkRequestResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::loginView(fn () => view('pages.auth.login'));

        Fortify::registerView(function () {
            abort_unless(app(GeneralSettings::class)->registration_enabled, 404);

            return view('pages.auth.register');
        });

        Fortify::requestPasswordResetLinkView(fn () => view('pages.auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('pages.auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('pages.auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('pages.auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('pages.auth.two-factor-challenge'));

        // Login dengan email atau username. Akun yang dinonaktifkan (soft delete) tidak ditemukan.
        Fortify::authenticateUsing(function (Request $request): ?User {
            $login = Str::lower(trim((string) $request->input('login')));
            $password = (string) $request->input('password');

            $user = User::query()
                ->where(fn ($q) => $q->where('email', $login)->orWhere('username', $login))
                ->first();

            if (! $user) {
                Hash::make($password); // samakan waktu respons agar akun tidak bisa ditebak lewat jeda

                return null;
            }

            return Hash::check($password, $user->password) ? $user : null;
        });

        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });

    }
}
