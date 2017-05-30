<?php

namespace ALS\Modules\Shipment\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use ALS\Modules\Report\Repositories\ReportRepository;
use ALS\Modules\Shipment\Models\Shipment;
use ALS\Modules\User\Repositories\UserRepository;
use Carbon\Carbon;

class ShipmentRepository extends BaseRepository
{
    protected $aliases = [
        'driver_id' => 'emp_driver_id',
    ];

    public function model()
    {
        return Shipment::class;
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
        $shipment  = $this->with($relations)->find($id)->toArray();

        if (! $shipment) {
            throw new \Exception('Record cannot be found');
        }

        $shipment['shipment_reason'] = [
            'shipment_reason_id'   => $shipment['shipment_reason']['id'] ?? '',
            'shipment_reason_name' => $shipment['shipment_reason']['value'] ?? '',
        ];

        $shipment['shipment_status'] = [
            'shipment_status_id'   => $shipment['shipment_status']['id'] ?? '',
            'shipment_status_key'  => $shipment['shipment_status']['key'] ?? '',
            'shipment_status_name' => $shipment['shipment_status']['value'] ?? '',
        ];

        $dictionaryRepo = $this->app->make(DictionaryRepository::class);

        foreach ($relations as $relation) {
            $relationKey = snake_case($relation);
            if (isset($relationKey) && is_null($shipment[snake_case($relation)])) {
                $shipment[$relationKey] = [];
            }
        }

        $return['pending_threshold']               = '3';
        $return['shipment_data']                   = $shipment;
        $return['dropdown']['status']              = $dictionaryRepo->get('shipment_status');
        $return['dropdown']['reason']['pending']   = $dictionaryRepo->get('reason', 'pending');
        $return['dropdown']['reason']['cancelled'] = $dictionaryRepo->get('reason', 'cancelled');
        $return['dropdown']['payment_method']      = $dictionaryRepo->get('pay_method');
        $return['dropdown']['messages']            = [
            'pending'          => $dictionaryRepo->get('sms_message', 'pending'),
            'cancelled'        => $dictionaryRepo->get('sms_message', 'cancelled'),
            'delivered'        => $dictionaryRepo->get('sms_message', 'delivered'),
            'out_for_delivery' => $dictionaryRepo->get('sms_message', 'out_for_delivery'),
            'picked-up'        => $dictionaryRepo->get('sms_message', 'picked-up'),
        ];

        return $return;
    }

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

        $data = $this->restQueryBuilder($fields, $filters, $sort, $relations, $limit, $dataKey)->toArray();

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
     *
     * @deprecated 1.1 Old API Helper
     *
     * @return array
     * @throws \Exception
     */
    public function getDriverShipments($requestRelations, $requestFilters)
    {
        $dictionaryRepo = $this->app->make(DictionaryRepository::class);
        $userRepo       = $this->app->make(UserRepository::class);
        $reportRepo     = $this->app->make(ReportRepository::class);

        $reportAcknowledgedStatus = $dictionaryRepo->get('report_status', 'acknowledged')->first();

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

        if (app('auth')->user()->hasRole('drivers')) {
            $driver        = app('auth')->user();
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
            $driver = $this->getDriverFromRequestFilter($requestFilters, $userRepo);
        }

        $todayBeginDate    = Carbon::today()->format('Y-m-d H:i:s');
        $tomorrowBeginDate = Carbon::tomorrow()->format('Y-m-d H:i:s');
        $customFilters[]   = ['field' => 'updated_at', 'compare' => '>', 'value' => $todayBeginDate];
        $customFilters[]   = ['field' => 'updated_at', 'compare' => '<', 'value' => $tomorrowBeginDate];
        $filters           = array_merge((array) $requestFilters, $customFilters);

        $driverReport = $reportRepo->findOrCreate($driver->id, null, false);

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
        $restQueryResult = $this->restQueryDriverShipments(null, $filters, null, $relations, 1000, 'shipments');
        $data            = array_merge($data, $restQueryResult);

        return $data;
    }

    /**
     * Get driver instance using the id from request filter
     *
     * @param $requestFilters
     * @param $userRepo
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriverFromRequestFilter($requestFilters, $userRepo)
    {
        if (in_array('driver_id', array_values(array_column($requestFilters, 'field')))) {
            foreach ($requestFilters as $filter) {
                if ($filter['field'] == 'driver_id') {
                    $driverID = $filter['value'];
                    break;
                }
            }
            $driver = $userRepo->find($driverID);

            return $driver;
        } else {
            throw new \Exception('Cannot detect driver', 400);
        }
    }
}