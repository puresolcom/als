<?php
namespace ALS\Modules\Option\Providers;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    static $routesPaths
        = [
            __DIR__ . '/../routes/routes.php'
        ];

    public function getModuleName(): string
    {
        return 'Option';
    }
}