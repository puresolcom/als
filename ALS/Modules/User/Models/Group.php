<?php

namespace ALS\Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table      = 'aw_group';
    public    $timestamps = false;
    protected $guarded    = [];
    protected $hidden     = ['pivot'];
}