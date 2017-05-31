<?php
namespace ALS\Modules\User\Providers;

use ALS\Modules\User\Services\Authentication;
use ALS\Modules\User\Services\User;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    static $routesPaths = [
        __DIR__.'/../routes/routes.php',
    ];

    public function getModuleName(): string
    {
        return 'User';
    }

    public function register()
    {
        parent::register();
        $this->app->bind('user', function () {
            return $this->app->make(User::class);
        });

        $this->app->bind('user.authentication', function () {
            return $this->app->make(Authentication::class);
        });
    }
}