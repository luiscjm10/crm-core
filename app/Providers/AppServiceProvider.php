<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        // Mantener tu optimización actual para el frontend en Vue
        Vite::prefetch(concurrency: 3);

        // Forzar HTTPS cuando la aplicación esté en el entorno de producción
        // Esto resuelve el Mixed Content detrás del proxy de Cloudflare y los contenedores
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
