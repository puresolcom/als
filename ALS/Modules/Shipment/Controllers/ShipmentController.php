<?php

namespace ALS\Modules\Shipment\Controllers;

use ALS\Core\Authorization\Exceptions\UnauthorizedAccess;
use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\Shipment\Services\Shipment;

/**
 * Class ShipmentController
 *
 * @package ALS\Modules\Shipment\Controllers
 */
class  ShipmentController extends Controller
{
    /**
     * @var Shipment $shipmentService ;
     */
    protected $shipmentService;

    public function __construct()
    {
        $this->shipmentService = app('shipment');
    }

    /**
     * Get Shipments
     *
     * @route /shipment [GET]
     *
     * @param \ALS\Core\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        try {
            $result = $this->shipmentService->getDriverShipments($request->getRelations(), $request->getFilters(), $request->getSort());
        } catch (\Exception $e) {
            return $this->jsonResponse(null, $e->getMessage(), $e->getCode() ?? 400);
        }

        return $this->jsonResponse($result);
    }

    /**
     * Get single shipment
     *
     * @route /shipment/{id} [GET]
     *
     * @param null $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getSingle($id = null)
    {
        try {
            $result = $this->shipmentService->getShipmentWithDriverAndDropdowns($id);
            if (! app('auth')->user()->hasRole('manage-driver') && ! app('auth')->user()->owns($result['shipment_data'], 'emp_driver_id')) {
                throw new UnauthorizedAccess();
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(null, $e->getMessage(), 400);
        }

        return $this->jsonResponse($result, 'Success');
    }

    /**
     * Advanced shipment listening
     *
     * @route /shipment/list [GET]
     *
     * @param \ALS\Core\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $restQuery = $this->shipmentService->restQueryBuilder($request->getFields(), $request->getFilters(), $request->getSort(), $request->getRelations(), $request->getPerPage(), 'shipments');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Request Failed', 400, $e->getMessage());
        }

        return $this->jsonResponse($restQuery);
    }
}