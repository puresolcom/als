<?php

namespace ALS\Modules\Expense\Providers;

use ALS\Modules\Expense\Services\Expense;

class ModuleServiceProvider extends \ALS\Providers\ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Expense';
    }

    public function register()
    {
        $this->app->bind('expense', function () {
            return $this->app->make(Expense::class);
        });
    }
}