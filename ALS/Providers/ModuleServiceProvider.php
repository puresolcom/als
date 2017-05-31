<?php

namespace ALS\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    static $routesPaths = [];

    public function register()
    {
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        $this->app->group([
            'prefix'    => strtolower($this->getModuleName()),
            'namespace' => 'ALS\\Modules\\'.$this->getModuleName().'\\Controllers',
        ], function ($app) {
            if (is_array(static::$routesPaths) || count(static::$routesPaths) != 0) {
                foreach (static::$routesPaths as $routePath) {
                    if (! file_exists($routePath)) {
                        continue;
                    }

                    require $routePath;
                }
            }
        });
    }

    abstract function getModuleName(): string;
}