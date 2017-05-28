<?php

namespace ALS\Providers;

use ALS\Core\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Request::class, function () {
            return Request::capture();
        });

        $this->app->alias(Request::class, 'request');
    }
}
