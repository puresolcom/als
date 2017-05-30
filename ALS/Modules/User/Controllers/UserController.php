<?php

namespace ALS\Modules\User\Controllers;

use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\User\Repositories\UserRepository;

/**
 * Class UserController
 *
 * @package ALS\Modules\User\Controllers
 */
class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    /**
     * Get driver summary
     *
     * @route /user/summary/{userID},/user/{userID}/summary, /user/summary
     *
     * @param \ALS\Core\Http\Request $request
     * @param null                   $userID
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request, $userID = null)
    {
        $isDriver = app('auth')->user()->hasRole('drivers');
        // Getting the driver instance
        if (is_null($userID) && $isDriver) {
            $driver = app('auth')->user();
        } elseif (is_int($userID)) {
            $driver = $this->userRepo->find($userID);
        }

        if (! isset($driver)) {
            return $this->jsonResponse(null, 'Invalid driver ID', 400);
        }

        dd($this->userRepo->getDriverSummary($driver->id, $isDriver)->toArray());
    }
}