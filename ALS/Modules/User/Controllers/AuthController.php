<?php

namespace ALS\Modules\User\Controllers;

use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\Option\Repositories\OptionRepository;
use ALS\Modules\User\Repositories\UserRepository;
use ALS\Repositories\TransientRepository;
use Carbon\Carbon;
use Firebase\JWT\JWT;

/**
 * Class AuthController
 *
 * @package ALS\Modules\User\Controllers
 */
class AuthController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * @var TransientRepository
     */
    protected $transientRepo;

    public function __construct(
        UserRepository $userRepo,
        TransientRepository $transientRepo
    ) {
        $this->userRepo      = $userRepo;
        $this->transientRepo = $transientRepo;
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
    public function login(Request $request, OptionRepository $optionRepo)
    {
        // Validate request
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        // Get user
        $userInstance = $this->userRepo->findByField('username', $request->input('username'), [
            'id',
            'name',
            'last_name',
            'password',
        ])->first();

        // Check if user exists
        if (is_null($userInstance)) {
            return $this->jsonResponse(null, 'User cannot be found', 400);
        }

        // Verify if password matches
        if (! password_verify($request->input('password'), $userInstance->password)) {
            return $this->jsonResponse(null, 'Invalid login credentials', 400);
        }

        // Getting Auth Options from DB
        $jwtKey                = $optionRepo->get('auth', 'jwt_key')->value;
        $tokenExpirationPeriod = $optionRepo->get('auth', 'token_expire_time')->value;
        $jwtEncryptionAlg      = $optionRepo->get('auth', 'jwt_encryption_algo')->value;

        // Generate JWT token
        $token = [
            'exp'  => time() + $tokenExpirationPeriod,
            'data' => [
                'user_id' => $userInstance->id,
            ],
        ];

        $jwt = JWT::encode($token, $jwtKey, $jwtEncryptionAlg);

        // Preparing output
        $output = [
            'token'      => $jwt,
            'name'       => $userInstance->getFullName(),
            'id'         => $userInstance->id,
            'user_group' => $userInstance->groups,
        ];

        // Saving token into transient table
        try {
            $this->transientRepo->create([
                'key'        => $userInstance->id,
                'value'      => $jwt,
                'expired_at' => Carbon::now()->addSeconds($tokenExpirationPeriod),
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Unable to log you in, please try again', 400, $e->getMessage());
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
        $currentLoggedInUser = app('auth')->user();
        $logOutAll           = $request->get('all') ?? false;

        if ($logOutAll) {
            // Remove all user tokens/sessions
            $this->transientRepo->deleteWhere(['key' => $currentLoggedInUser->id]);
        } else {
            // Remove only current token/session
            $this->transientRepo->deleteWhere([
                'key'   => $currentLoggedInUser->id,
                'value' => $request->header('Authorization'),
            ]);
        }

        return $this->jsonResponse(null, 'Logged out successfully');
    }
}