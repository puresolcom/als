<?php

namespace ALS\Modules\User\Models;

use ALS\Core\Authorization\Traits\UserTrait;
use ALS\Core\Eloquent\Model;
use ALS\Modules\Shipment\Models\Shipment;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use UserTrait, Authenticatable, Authorizable;

    protected $table = 'aw_user';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'emp_driver_id', 'id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'aw_user_group', 'user_id', 'group_id');
    }

    public function getFullName()
    {
        return implode(' ', [$this->name, $this->last_name]);
    }
}
