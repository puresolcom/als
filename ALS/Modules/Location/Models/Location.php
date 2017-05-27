<?php

namespace ALS\Modules\Location\Models;

use ALS\Core\Eloquent\Model;

class Location extends Model
{
    public    $timestamps = false;
    protected $table      = 'aw_location';
    protected $guarded    = [];
}