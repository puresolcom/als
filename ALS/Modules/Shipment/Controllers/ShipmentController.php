<?php

namespace ALS\Modules\Shipment\Controllers;

use ALS\Http\Controllers\Controller;
use Illuminate\Http\Request;


/**
 * Class ShipmentController
 * @package ALS\Modules\Shipment\Controllers
 */
class  ShipmentController extends Controller
{
    public function get(Request $request, $shipmentId)
    {
        return 'shipment index';
    }
}