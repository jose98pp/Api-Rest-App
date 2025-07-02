<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        // Habilitar CORS para permitir la comunicación entre backend y frontend
        \Illuminate\Support\Facades\Route::middleware(['cors'])->group(function () {
            // Aquí puedes definir tus rutas protegidas por CORS
            // Ejemplo:
            // Route::get('/api/data', [DataController::class, 'index']);
        });
    }
}
