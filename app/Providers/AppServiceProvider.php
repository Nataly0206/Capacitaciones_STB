<?php

namespace App\Providers;

use App\Support\CarbonResiliente;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Throwable;

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

        try {
            if (config('database.default') === 'sqlsrv' && extension_loaded('pdo_sqlsrv')) {
                DB::statement('SET DATEFORMAT ymd');
            }
        } catch (Throwable $e) {
            // Evita que comandos Artisan o entornos sin SQL Server fallen al iniciar la app.
        }

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonResiliente::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        $regla = Password::min(12)
            ->mixedCase()
            ->letters()
            ->numbers()
            ->symbols();

        if (app()->isProduction()) {
            $regla->uncompromised();
        }

        Password::defaults(fn (): Password => $regla);
    }
}
