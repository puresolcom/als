<?php

namespace ALS\Modules\Shipment\Services;

use ALS\Modules\Shipment\Repositories\ShipmentRepository;
use ALS\Modules\User\Services\User;
use Carbon\Carbon;
use Laravel\Lumen\Application;

/**
 * Shipment Service
 *
 * @package ALS\Modules\Shipment\Services
 */
class Shipment
{
    protected $app;

    protected $shipmentRepo;

    protected $aliases = [
        'driver_id' => 'emp_driver_id',
    ];

    public function __construct(Application $app, ShipmentRepository $shipmentRepo)
    {
        $this->app          = $app;
        $this->shipmentRepo = $shipmentRepo;
    }

    /**
     * Prepares a restful query
     *
     * @param null   $fields
     * @param null   $filters
     * @param null   $sort
     * @param null   $relations
     * @param int    $limit
     * @param string $dataKey
     *
     * @return mixed
     */
    public function restQueryBuilder(
        $fields = null,
        $filters = null,
        $sort = null,
        $relations = null,
        $limit = null,
        $dataKey = 'data'
    ) {
        return $this->shipmentRepo->restQueryBuilder($fields, $filters, $sort, $relations, $limit, $dataKey);
    }

    /**
     * Get shipment along with its driver details, products and dropdowns and any related content
     *
     * @param                                                           $id
     *
     * @deprecated 1.1.0 Used to support old API consumers
     *
     * @return mixed
     * @throws \Exception
     */
    public function getShipmentWithDriverAndDropdowns($id)
    {
        $relations = ['products', 'driverDetails', 'shipmentPaymentMethod', 'shipmentStatus', 'shipmentReason'];
        $shipment  = $this->shipmentRepo->with($relations)->find($id)->toArray();

        if (! $shipment) {
            throw new \Exception('Record cannot be found');
        }

        $dictionaryService = $this->app->make('dictionary');

        foreach ($relations as $relation) {
            $relationKey = snake_case($relation);
            if (isset($relationKey) && is_null($shipment[snake_case($relation)])) {
                $shipment[$relationKey] = [];
            }
        }

        $return['pending_threshold']               = '3';
        $return['shipment_data']                   = &$shipment;
        $return['products']                        = $shipment['products'];
        $return['shipment_payment_method']         = $shipment['shipment_payment_method'];
        $return['shipment_reason']                 = [
            'shipment_reason_id'   => $shipment['shipment_reason']['id'] ?? '',
            'shipment_reason_name' => $shipment['shipment_reason']['value'] ?? '',
        ];
        $return['shipment_status']                 = [
            'shipment_status_id'   => $shipment['shipment_status']['id'] ?? '',
            'shipment_status_key'  => $shipment['shipment_status']['key'] ?? '',
            'shipment_status_name' => $shipment['shipment_status']['value'] ?? '',
        ];
        $return['driver_details']                  = $shipment['driver_details'];
        $return['dropdown']['status']              = $dictionaryService->get('shipment_status');
        $return['dropdown']['reason']['pending']   = $dictionaryService->get('reason', 'pending');
        $return['dropdown']['reason']['cancelled'] = $dictionaryService->get('reason', 'cancelled');
        $return['dropdown']['payment_method']      = $dictionaryService->get('pay_method');
        $return['dropdown']['messages']            = [
            'pending'          => $dictionaryService->get('sms_message', 'pending'),
            'cancelled'        => $dictionaryService->get('sms_message', 'cancelled'),
            'delivered'        => $dictionaryService->get('sms_message', 'delivered'),
            'out_for_delivery' => $dictionaryService->get('sms_message', 'out_for_delivery'),
            'picked-up'        => $dictionaryService->get('sms_message', 'picked-up'),
        ];

        if (empty($shipment['additional_data'])) {
            unset($shipment['additional_data']);
        } else {
            $shipment['additional_data'] = unserialize($shipment['additional_data']);
            if (! is_array($shipment['additional_data']) && ! is_object($shipment['additional_data'])) {
                unset($shipment['additional_data']);
            }
        }

        // due amount for collection
        $amountDue              = ( float ) $shipment['amount_order'] - ( float ) $shipment['amount_paid'] - ( float ) $shipment['amount_collected'];
        $shipment['amount_due'] = number_format($amountDue, '2', '.', '');
        unset($shipment['shipment_reason'], $shipment['shipment_status'], $shipment['products'], $shipment['shipment_payment_method'], $shipment['driver_details']);

        return $return;
    }

    /**
     * Prepares a Restful shipment query for older API Versions
     *
     * @param null   $fields
     * @param null   $filters
     * @param null   $sort
     * @param null   $relations
     * @param null   $limit
     * @param string $dataKey
     *
     * @deprecated 1.1.0 Used to support old API consumers
     *
     * @return mixed
     */
    public function restQueryDriverShipments(
        $fields = null,
        $filters = null,
        $sort = null,
        $relations = null,
        $limit = null,
        $dataKey = 'data'
    ) {
        // Mapping old API filter field names to real names
        $filters = $this->mapFiltersAliases($filters);

        $data = $this->shipmentRepo->restQueryBuilder($fields, $filters, $sort, $relations, $limit, $dataKey)->toArray();

        foreach ($data[$dataKey] as $i => $record) {
            $data[$dataKey][$i]['status']      = $data[$dataKey][$i]['status']['value'];
            $data[$dataKey][$i]['reason']      = $data[$dataKey][$i]['reason']['value'];
            $data[$dataKey][$i]['assigned_by'] = $data[$dataKey][$i]['assigner']['name'].' '.$data[$dataKey][$i]['assigner']['last_name'];
            $data[$dataKey][$i]['in_transit']  = is_array($data[$dataKey][$i]['transit']);

            // Converting nested location to flat location to support old API consumers
            if (isset($record['location'])) {
                $data[$dataKey][$i]['region'] = array_filter($record['location'], function ($data) {
                    return ! is_array($data);
                });
                $data[$dataKey][$i]['city']   = array_filter($record['location']['recursive_parents'], function ($data
                ) {
                    return ! is_array($data);
                });

                $data[$dataKey][$i]['country'] = array_filter($record['location']['recursive_parents']['recursive_parents'], function (
                    $data
                ) {
                    return ! is_array($data);
                });

                unset($data[$dataKey][$i]['location']);
            }
        }

        return $data;
    }

    /**
     * Maps request filter aliases to it's corresponding field name
     *
     * @param $filters
     *
     * @return array|bool
     */
    protected function mapFiltersAliases($filters)
    {

        if (! is_array($filters)) {
            return false;
        }

        $analyzedFilters = [];
        foreach ($filters as $filter) {
            if (array_key_exists($filter['field'], $this->aliases)) {
                $filter['field']   = $this->aliases[$filter['field']];
                $analyzedFilters[] = $filter;

                continue;
            }
            $analyzedFilters[] = $filter;
        }

        return $analyzedFilters;
    }

    /**
     * Get Driver shipments along with all of its relations
     *
     * @param $requestRelations
     * @param $requestFilters
     * @param $requestSort
     *
     * @deprecated 1.1 Old API Helper
     *
     * @return array
     * @throws \Exception
     */
    public function getDriverShipments($requestRelations, $requestFilters, $requestSort)
    {
        // Preparing services and repositories
        $dictionaryService = $this->app->make('dictionary');
        $userService       = $this->app->make('user');
        $reportService     = $this->app->make('report');

        $reportAcknowledgedStatus = $dictionaryService->get('report_status', 'acknowledged')->first();

        // Required Relations
        $customRelations = [
            ['relationName' => 'location', 'relationFields' => []],
            ['relationName' => 'location.recursiveParents', 'relationFields' => []],
            ['relationName' => 'status', 'relationFields' => ['id', 'value']],
            ['relationName' => 'reason', 'relationFields' => ['id', 'value']],
            ['relationName' => 'assigner', 'relationFields' => ['id', 'name', 'last_name']],
            ['relationName' => 'transit', 'relationFields' => []],
        ];

        $relations = array_merge((array) $requestRelations, $customRelations);

        $customFilters = [];

        // Custom drivers only filters
        if ($this->app->make('auth')->user()->hasRole('drivers')) {
            $driver        = $this->app->make('auth')->user();
            $customFilters = [
                ['field' => 'verified_by', 'compare' => '!=', 'value' => 0],
                ['field' => 'verified_by', 'compare' => '!=', 'value' => null],
                [
                    'relational'   => true,
                    'relationName' => 'report',
                    'field'        => 'status_id',
                    'compare'      => ':',
                    'value'        => $reportAcknowledgedStatus->id,
                ],
            ];
        } else {
            if (! is_array($requestFilters)) {
                throw new \Exception('Request filters has to be set', 400);
            }
            $driver = $this->getDriverFromRequestFilter($requestFilters, $userService);
        }

        // General Filters
        $todayBeginDate    = Carbon::today()->format('Y-m-d H:i:s');
        $tomorrowBeginDate = Carbon::tomorrow()->format('Y-m-d H:i:s');
        $customFilters[]   = ['field' => 'updated_at', 'compare' => '>', 'value' => $todayBeginDate];
        $customFilters[]   = ['field' => 'updated_at', 'compare' => '<', 'value' => $tomorrowBeginDate];
        $filters           = array_merge((array) $requestFilters, $customFilters);

        $driverReport = $reportService->findOrCreate($driver->id, null, false);

        $data              = [];
        $data['shipments'] = [];
        $data['driver']    = $driver;

        if (! $driverReport) {
            throw new \Exception('No driver report can be found', 200);
        }

        $data['driver']['report_id']     = $driverReport->id;
        $data['driver']['report_status'] = $driverReport->status;

        if ($driverReport->status->id == $reportAcknowledgedStatus->id && app('auth')->user()->hasRole('manage-driver')) {
            throw new \Exception('Report is in acknowledged status', 200);
        }

        // Override sorting case of (location_id, status_id)
        $sort = [];
        foreach ($requestSort as $i => $order) {
            switch ($order['orderBy']) {
                case 'location_id':
                    $order['orderBy'] = 'location.name';
                    $sort[]           = $order;
                    break;
                case 'status_id':
                    $order['orderBy'] = 'status.key';
                    $sort[]           = $order;
                    break;
            }
        }
        
        $restQueryResult = $this->restQueryDriverShipments(null, $filters, $sort, $relations, 99999, 'shipments');
        $data            = array_merge($data, $restQueryResult);

        return $data;
    }

    /**
     * Get driver instance using the id from request filter
     *
     * @param array $requestFilters
     * @param User  $userService
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriverFromRequestFilter(array $requestFilters, $userService)
    {
        $driverID = null;
        if (in_array('driver_id', array_values(array_column($requestFilters, 'field')))) {
            foreach ($requestFilters as $filter) {
                if ($filter['field'] == 'driver_id') {
                    $driverID = $filter['value'];
                    break;
                }
            }
            $driver = $userService->find($driverID);

            return $driver;
        } else {
            throw new \Exception('Cannot detect driver', 400);
        }
    }
}