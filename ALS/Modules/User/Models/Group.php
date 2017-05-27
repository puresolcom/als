<?php

namespace ALS\Modules\User\Models;

use ALS\Core\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;

    protected $table = 'aw_group';

    protected $guarded = [ ];

    protected $hidden = [ 'pivot' ];
}