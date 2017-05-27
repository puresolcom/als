<?php

namespace ALS\Core\Authorization\Middleware;

use ALS\Core\Support\RestfulResponseTrait;

class RoleMiddleware
{
    use RestfulResponseTrait;

    public function handle($request, \Closure $next, $role)
    {
        $roles = explode('|', $role);
        if (!app('authorization')->hasRole($roles)) {
            return $this->jsonResponse(null, 'Unauthorized access to this resource', 400);
        }

        return $next($request);
    }
}