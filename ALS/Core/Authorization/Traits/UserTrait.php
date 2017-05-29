<?php

namespace ALS\Core\Authorization\Traits;

use ALS\Core\Eloquent\Model;

trait UserTrait
{
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
        if (is_array($name)) {
            return $this->checkMultipleRoles($name, $requireAll);
        } else {
            if (in_array($name, array_column($this->getRoles()->toArray(), 'name'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current user roles
     *
     * @return mixed
     */
    public function getRoles()
    {
        return $this->{config('auth.authorization.user_role_relation_name')};
    }

    /**
     * Verify if user owns an object
     *
     * @param Model|array $object
     * @param string      $referenceKey
     *
     * @return bool
     */
    public function owns($object, $referenceKey = 'user_id')
    {
        if (is_array($object)) {
            return $this->id == $object[$referenceKey];
        } else {
            return $this->id == $object->{$referenceKey};
        }
    }

    /**
     * Check if user has one or more of the passed roles
     *
     * @param array $rolesNames
     * @param bool  $requireAll
     *
     * @return bool
     */
    protected function checkMultipleRoles(array $rolesNames, bool $requireAll)
    {
        foreach ($rolesNames as $roleName) {
            $hasRole = $this->hasRole($roleName);

            if ($hasRole && ! $requireAll) {
                return true;
            } elseif (! $hasRole && $requireAll) {
                return false;
            }
        }

        return $requireAll;
    }
}