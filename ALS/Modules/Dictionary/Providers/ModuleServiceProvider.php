<?php

namespace ALS\Modules\Dictionary\Providers;

use ALS\Modules\Dictionary\Services\Dictionary;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    function getModuleName(): string
    {
        return 'Dictionary';
    }

    public function register()
    {
        parent::register();
        $this->app->bind('dictionary', function () {
            return $this->app->make(Dictionary::class);
        });
    }
}