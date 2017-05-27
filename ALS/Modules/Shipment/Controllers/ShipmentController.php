<?php

namespace ALS\Modules\Shipment\Controllers;

use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\Shipment\Repositories\ShipmentRepository;

/**
 * Class ShipmentController
 *
 * @package ALS\Modules\Shipment\Controllers
 */
class  ShipmentController extends Controller
{
    /**
     * @var ShipmentRepository
     */
    protected $shipmentRepo;

    public function __construct(ShipmentRepository $shipmentRepo)
    {
        $this->shipmentRepo = $shipmentRepo;
    }

    public function get(Request $request, $shipmentId)
    {
        return 'shipment index';
    }

    public function list(Request $request)
    {
        try {
            $restQuery = $this->shipmentRepo->restQueryBuilder($request->getFields(), $request->getFilters(), $request->getSort(), $request->getRelations(), $request->getPerPage(), 'shipments');
        }catch (\Exception $e) {
            return $this->jsonResponse($e->getMessage(), 'Request Failed', 400);
        }

        return $this->jsonResponse($restQuery);
    }
}