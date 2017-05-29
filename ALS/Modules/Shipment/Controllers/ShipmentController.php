<?php

namespace ALS\Modules\Shipment\Controllers;

use ALS\Core\Authorization\Exceptions\UnauthorizedAccess;
use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use ALS\Modules\Report\Repositories\ReportRepository;
use ALS\Modules\Shipment\Repositories\ShipmentRepository;
use ALS\Modules\User\Repositories\UserRepository;

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

    public function get(
        Request $request,
        DictionaryRepository $dictionaryRepo,
        ReportRepository $reportRepo,
        UserRepository $userRepo,
        $id = null
    ) {
        // Show driver active verified shipments
        if (app('auth')->user()->hasRole(['drivers', 'manage-driver']) && $id === null) {
            try {
                return $this->jsonResponse($this->shipmentRepo->getDriverShipments($request->getRelations(), $request->getFilters()));
            } catch (\Exception $e) {
                return $this->jsonResponse(null, $e->getMessage(), $e->getCode() ?? 400);
            }
            // Show driver active shipments
        } elseif ($id !== null) {
            try {
                $data = $this->shipmentRepo->getShipmentWithDriverAndDropdowns($id, $dictionaryRepo);
                if (! app('auth')->user()->hasRole('manage-driver') && ! app('auth')->user()->owns($data['shipment_data'], 'emp_driver_id')) {
                    throw new UnauthorizedAccess();
                }
            } catch (\Exception $e) {
                return $this->jsonResponse(null, $e->getMessage(), 400);
            }

            return $this->jsonResponse($data);
        } else {
            return $this->list($request);
        }
    }

    public function list(Request $request)
    {
        try {
            $restQuery = $this->shipmentRepo->restQueryBuilder($request->getFields(), $request->getFilters(), $request->getSort(), $request->getRelations(), $request->getPerPage(), 'shipments');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Request Failed', 400, $e->getMessage());
        }

        return $this->jsonResponse($restQuery);
    }
}