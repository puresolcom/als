<?php
namespace ALS\Modules\Option\Providers;

use ALS\Modules\Option\Services\Option;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    static $routesPaths = [
        __DIR__.'/../routes/routes.php',
    ];

    public function getModuleName(): string
    {
        return 'Option';
    }

    public function register()
    {
        parent::register();
        $this->app->bind('option', function () {
            return $this->app->make(Option::class);
        });
    }
}