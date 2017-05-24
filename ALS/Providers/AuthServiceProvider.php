<?php

namespace ALS\Providers;

use ALS\Core\Support\RestfulResponseTrait;
use ALS\Modules\User\Repositories\UserRepository;
use ALS\Repositories\TransientRepository;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use RestfulResponseTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {

        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request){

            // Verify Authorization header is passed
            if ($request->header('Authorization')) {

                // Init Repositories
                $transientRepo = $this->app->make(TransientRepository::class);
                $userRepo      = $this->app->make(UserRepository::class);
                $token         = $transientRepo->findByField('value', $request->header('Authorization'))->first();

                // If token does not exists or expired
                if (is_null($token) || Carbon::createFromFormat('Y-m-d H:i:s',
                        $token->expired_at)->getTimestamp() < Carbon::now()->getTimestamp()
                ) {
                    return null;
                }

                // If everything goes well return user instance
                return $userRepo->find((int)$token->key) ?? null;
            }
            return null;
        });
    }
}
