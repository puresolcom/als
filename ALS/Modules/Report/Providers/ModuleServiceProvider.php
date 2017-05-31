<?php

namespace ALS\Modules\Report\Providers;

use ALS\Modules\Report\Services\Report;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Report';
    }

    public function register()
    {
        parent::register();
        $this->app->bind('report', function () {
            return $this->app->make(Report::class);
        });
    }
}