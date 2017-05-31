<?php

namespace ALS\Modules\User\Services;

use ALS\Modules\User\Repositories\UserRepository;
use ALS\Repositories\TransientRepository;
use Carbon\Carbon;
use Firebase\JWT\JWT;

/**
 * User Authentication Service
 *
 * @package ALS\Modules\User\Services
 */
class Authentication
{
    /**
     * Authenticate User
     *
     * @param array $credentials
     *
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials)
    {

        $userRepo = app(UserRepository::class);
        // Get user
        $userInstance = $userRepo->findByField('username', $credentials['username'], [
            'id',
            'name',
            'last_name',
            'password',
        ])->first();

        // Check if user exists
        if (is_null($userInstance)) {
            throw new \Exception('User cannot be found', 400);
        }

        // Verify if password matches
        if (! password_verify($credentials['password'], $userInstance->password)) {
            throw new \Exception('Invalid login credentials', 400);
        }

        $optionService = app('option');

        // Getting Auth Options from DB
        $jwtKey                = $optionService->get('auth', 'jwt_key')->value;
        $tokenExpirationPeriod = $optionService->get('auth', 'token_expire_time')->value;
        $jwtEncryptionAlg      = $optionService->get('auth', 'jwt_encryption_algo')->value;

        // Generate JWT token
        $token = ['exp' => time() + $tokenExpirationPeriod, 'data' => ['user_id' => $userInstance->id]];

        $jwt = JWT::encode($token, $jwtKey, $jwtEncryptionAlg);

        // Preparing output
        $output = [
            'token'      => $jwt,
            'name'       => $userInstance->getFullName(),
            'id'         => $userInstance->id,
            'user_group' => $userInstance->groups,
        ];

        // Saving token into transient table
        $transient = app(TransientRepository::class);
        try {
            $transient->create([
                'key'        => $userInstance->id,
                'value'      => $jwt,
                'expired_at' => Carbon::now()->addSeconds($tokenExpirationPeriod),
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }

        return $output;
    }

    public function logout($logOutAll = false, $authenticationToken)
    {
        $currentLoggedInUser = app('auth')->user();
        $transientRepo       = app(TransientRepository::class);

        if ($logOutAll) {
            // Remove all user tokens/sessions
            $deleted = $transientRepo->deleteWhere(['key' => $currentLoggedInUser->id]);
        } else {
            // Remove only current token/session
            $deleted = $transientRepo->deleteWhere([
                'key'   => $currentLoggedInUser->id,
                'value' => $authenticationToken,
            ]);
        }

        if ($deleted) {
            return true;
        }

        return false;
    }
}