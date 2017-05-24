<?php

namespace ALS\Modules\Option\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table      = 'aw_option';
    protected $guarded    = [];
    public    $timestamps = false;
}