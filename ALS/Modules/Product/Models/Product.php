<?php

namespace ALS\Modules\Product\Models;

class Product extends \ALS\Core\Eloquent\Model
{
    public $timestamps = false;

    protected $table = 'aw_shipment_product';

    protected $guarded = [];
}