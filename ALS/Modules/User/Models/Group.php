<?php

namespace ALS\Modules\User\Models;

use ALS\Core\Eloquent\Model;

class Group extends Model
{
    protected $table      = 'aw_group';
    public    $timestamps = false;
    protected $guarded    = [];
    protected $hidden     = ['pivot'];
}