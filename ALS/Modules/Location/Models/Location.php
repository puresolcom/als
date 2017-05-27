<?php

namespace ALS\Modules\Location\Models;

use ALS\Core\Eloquent\Model;

class Location extends Model
{
    protected $table      = 'aw_location';
    protected $guarded    = [];
    public    $timestamps = false;
}