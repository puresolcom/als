<?php

namespace ALS\Modules\Option\Models;

use ALS\Core\Eloquent\Model;

class Option extends Model
{
    protected $table      = 'aw_option';
    protected $guarded    = [];
    public    $timestamps = false;
}