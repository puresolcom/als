<?php

namespace ALS\Modules\Shipment\Controllers;

use ALS\Core\Authorization\Exceptions\UnauthorizedAccess;
use ALS\Core\Http\Request;
use ALS\Http\Controllers\Controller;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
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

    public function get(Request $request, DictionaryRepository $dictionaryRepo, $id = null)
    {
        // Show driver active shipments
        if (app('auth')->user()->hasRole('drivers') && $id === null) {
            return $this->getDriverShipments($request, $dictionaryRepo);
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

    protected function getDriverShipments(Request $request, DictionaryRepository $dictionaryRepo)
    {

        $requestRelations = $request->getRelations() ?? [];
        $requestFilters   = $request->getFilters() ?? [];

        // Required Relations
        $customRelations = [
            [
                'relationName'   => 'location',
                'relationFields' => [],
            ],
            [
                'relationName'   => 'location.recursiveParents',
                'relationFields' => [],
            ],
            [
                'relationName'   => 'status',
                'relationFields' => ['id', 'value'],
            ],
        ];

        $relations = array_merge($requestRelations, $customRelations);

        $customFilters = [
            [
                'relational'   => true,
                'relationName' => 'report',
                'field'        => 'status_id',
                'compare'      => ':',
                'value'        => $dictionaryRepo->get('report_status', 'acknowledged')->first()->id,
            ],
        ];

        $filters = array_merge($requestFilters, $customFilters);

        try {
            $restQuery = $this->shipmentRepo->restQueryDriverShipments($request->getFields(), $filters, $request->getSort(), $relations, $request->getPerPage(), 'shipments');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Request Failed', 400, $e->getMessage());
        }

        return $this->jsonResponse($restQuery);
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