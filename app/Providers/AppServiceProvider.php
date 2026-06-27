<?php

namespace App\Providers;

use App\Domains\Notifications\Events\TicketClosed;
use App\Domains\Notifications\Events\TicketCommented;
use App\Domains\Notifications\Events\TicketCreated;
use App\Domains\Notifications\Listeners\SendPushNotification;
use App\Domains\Notifications\Listeners\StoreDatabaseNotification;
use Illuminate\Support\Facades\Event;
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
        Vite::prefetch(concurrency: 3);

        Event::listen(TicketCreated::class, SendPushNotification::class);
        Event::listen(TicketCreated::class, StoreDatabaseNotification::class);
        Event::listen(TicketCommented::class, SendPushNotification::class);
        Event::listen(TicketCommented::class, StoreDatabaseNotification::class);
        Event::listen(TicketClosed::class, SendPushNotification::class);
        Event::listen(TicketClosed::class, StoreDatabaseNotification::class);

        // Forzar HTTPS cuando la aplicación esté en el entorno de producción
        // Esto resuelve el Mixed Content detrás del proxy de Cloudflare y los contenedores
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
