<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Throw an exception whenever a relationship is lazy loaded (dev only).
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
