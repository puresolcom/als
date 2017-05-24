<?php

namespace ALS\Modules\Shipment\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $table      = 'aw_shipment';
    protected $guarded    = [];
    public    $timestamps = true;
}