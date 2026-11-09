<?php

namespace App\Foundation\IO;

use App\IAM\Entities\Passkey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passkeys\Passkeys;

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
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Passkeys::usePasskeyModel(Passkey::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction() && ! $this->isForcedOnTheCommandLine(),
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

    private function isForcedOnTheCommandLine(): bool
    {
        return app()->runningInConsole() && in_array('--force', $_SERVER['argv'] ?? [], true);
    }
}
