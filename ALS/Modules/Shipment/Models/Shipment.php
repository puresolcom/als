<?php

namespace ALS\Modules\Shipment\Models;

use ALS\Core\Eloquent\Model;
use ALS\Modules\Location\Models\Location;
use ALS\Modules\User\Models\User;

class Shipment extends Model
{
    protected $table      = 'aw_shipment';
    protected $guarded    = [];
    public    $timestamps = true;

    public function driver()
    {
        return $this->belongsTo(User::class, 'emp_driver_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }
}