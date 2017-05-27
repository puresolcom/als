<?php

namespace ALS\Core\Authorization;

use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'authorization', function ($app) {
            return new Authorization($app);
        }
        );
    }
}