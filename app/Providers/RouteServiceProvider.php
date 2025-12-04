<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
          // Web Routes
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Admin Routes
        Route::middleware(['web', 'auth', 'role:admin|operator'])
            ->prefix('')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    }
}
