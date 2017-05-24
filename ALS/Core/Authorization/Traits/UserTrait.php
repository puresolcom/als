<?php

namespace ALS\Core\Authorization\Traits;

use Illuminate\Database\Eloquent\Model;

trait UserTrait
{

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
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                }elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        }else {
            foreach ($this->getRoles() as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verify if user owns an object
     *
     * @param Model  $object
     * @param string $referenceKey
     *
     * @return bool
     */
    public function owns(Model $object, $referenceKey = 'user_id')
    {
        return $this->id == $object->{$referenceKey};
    }
}