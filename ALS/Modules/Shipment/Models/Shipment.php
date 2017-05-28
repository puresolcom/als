<?php

namespace ALS\Modules\Shipment\Models;

use ALS\Core\Eloquent\Model;
use ALS\Modules\Dictionary\Models\Dictionary;
use ALS\Modules\Location\Models\Location;
use ALS\Modules\Product\Models\Product;
use ALS\Modules\Report\Models\Report;
use ALS\Modules\User\Models\User;

class Shipment extends Model
{
    public $timestamps = true;

    protected $table = 'aw_shipment';

    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(User::class, 'emp_driver_id', 'id');
    }

    public function driverDetails()
    {
        return $this->driver();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shipment_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function paymentMethod()
    {
        return $this->hasOne(Dictionary::class, 'id', 'pay_method');
    }

    public function shipmentPaymentMethod()
    {
        return $this->paymentMethod();
    }

    public function reason()
    {
        return $this->hasOne(Dictionary::class, 'id', 'reason_id');
    }

    public function shipmentReason()
    {
        return $this->reason();
    }

    public function status()
    {
        return $this->hasOne(Dictionary::class, 'id', 'status_id');
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'id', 'report_id');
    }

    public function shipmentStatus()
    {
        return $this->status();
    }
}