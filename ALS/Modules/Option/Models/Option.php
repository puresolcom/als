<?php

namespace ALS\Modules\Option\Models;

use ALS\Core\Eloquent\Model;

class Option extends Model
{
    public    $timestamps = false;
    protected $table      = 'aw_option';
    protected $guarded    = [];
}