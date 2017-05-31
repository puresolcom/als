<?php

namespace ALS\Modules\User\Controllers;

use ALS\Http\Controllers\Controller;
use ALS\Modules\User\Services\User;

/**
 * Class UserController
 *
 * @package ALS\Modules\User\Controllers
 */
class UserController extends Controller
{
    /**
     * @var User $userService
     */
    protected $userService;

    public function __construct()
    {
        $this->userService = app('user');
    }

    /**
     * Get driver summary
     *
     * @route /user/summary/{userID},/user/{userID}/summary, /user/summary
     *
     * @param null $userID
     *
     * @return \Illuminate\Http\Response
     */
    public function summary($userID = null)
    {
        $isDriver = app('auth')->user()->hasRole('drivers');
        // Getting the driver instance
        if (is_null($userID) && $isDriver) {
            $driver = app('auth')->user();
        } elseif (is_int($userID)) {
            $driver = $this->userService->find($userID);
        }

        if (! isset($driver)) {
            return $this->jsonResponse(null, 'Invalid driver ID', 400);
        }

        $summaryResult = $this->userService->getDriverSummary($driver->id, $isDriver);

        if (! $summaryResult) {
            return $this->jsonResponse(null, 'Summary does not exist', 400);
        }

        return $this->jsonResponse($summaryResult, 'Driver summary processed');
    }
}