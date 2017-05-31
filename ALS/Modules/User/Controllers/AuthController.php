<?php

namespace ALS\Modules\User\Controllers;

use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\User\Services\Authentication;

/**
 * Class AuthController
 *
 * @package ALS\Modules\User\Controllers
 */
class AuthController extends Controller
{
    /**
     * @var Authentication
     */
    protected $userAuthenticationService;

    public function __construct()
    {
        $this->userAuthenticationService = app('user.authentication');
    }

    /**
     * User login
     *
     * @route user/auth/login [POST]
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        try {
            $output = $this->userAuthenticationService->login($credentials);
        } catch (\Exception $e) {
            return $this->jsonResponse(null, $e->getMessage(), $e->getCode());
        }

        return $this->jsonResponse($output, 'Logged in successfully');
    }

    /**
     * Logs a user out
     *
     * @route /user/auth/logout [GET,POST]
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $logOutAll           = $request->get('all') ?? false;
        $authenticationToken = $request->header('Authorization');

        $loggedOut = $this->userAuthenticationService->logout($logOutAll, $authenticationToken);

        if (! $loggedOut) {
            $this->jsonResponse(null, 'Unable to log you out, please try again later', 400);
        }

        return $this->jsonResponse(null, 'Logged out successfully');
    }
}