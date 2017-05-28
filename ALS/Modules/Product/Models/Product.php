<?php

namespace ALS\Modules\Product\Models;

class Product extends \ALS\Core\Eloquent\Model
{
    protected $table = 'aw_shipment_product';

    protected $guarded = [];

    public $timestamps = false;
}