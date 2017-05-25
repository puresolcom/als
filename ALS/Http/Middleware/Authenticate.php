<?php

namespace ALS\Http\Middleware;

use ALS\Core\Support\RestfulResponseTrait;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    use RestfulResponseTrait;
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \ALS\Core\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return $this->jsonResponse(null, 'Session Invalid/Expired. Please login again', 401);
        }

        return $next($request);
    }
}
