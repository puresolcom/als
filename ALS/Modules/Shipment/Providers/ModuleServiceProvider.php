<?php
namespace ALS\Modules\Shipment\Providers;

use ALS\Modules\Shipment\Services\Shipment;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    static $routesPaths = [
        __DIR__.'/../routes/routes.php',
    ];

    public function getModuleName(): string
    {
        return 'Shipment';
    }

    public function register()
    {
        parent::register();
        $this->app->bind('shipment', function () {
            return $this->app->make(Shipment::class);
        });
    }

    public function boot()
    {
    }
}