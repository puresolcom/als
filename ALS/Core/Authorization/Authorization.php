<?php

namespace ALS\Core\Authorization;

use ALS\Core\Eloquent\Model;
use Laravel\Lumen\Application;

/**
 * Class Authorization
 * @package ALS\Core\Authorization
 */
class Authorization
{

    /**
     * App Instance
     * @var Application
     */
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get user instance
     *
     * @return mixed
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    /**
     * Check if the user has role
     *
     * @param      $name
     * @param bool $requireAll
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if ($user = $this->user()) {
            return $user->hasRole($name, $requireAll);
        }
        return false;
    }

    public function owns(Model $object, $referenceKey = 'user_id')
    {
        if ($user = $this->user()) {
            return $user->owns($object, $referenceKey);
        }
        return false;
    }
}