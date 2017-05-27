<?php

namespace ALS\Modules\User\Controllers;

use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\User\Repositories\UserRepository;


/**
 * Class UserController
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

    public function list(Request $request)
    {

    }

    public function get()
    {

    }

}