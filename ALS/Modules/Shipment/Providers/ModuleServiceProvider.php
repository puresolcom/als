<?php
namespace ALS\Modules\Shipment\Providers;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    static $routesPaths = [
        __DIR__.'/../routes/routes.php',
    ];

    public function getModuleName(): string
    {
        return 'Shipment';
    }

    public function boot()
    {
    }
}