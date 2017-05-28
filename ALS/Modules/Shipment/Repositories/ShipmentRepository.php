<?php

namespace ALS\Modules\Shipment\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use ALS\Modules\Shipment\Models\Shipment;

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
     * @param \ALS\Modules\Dictionary\Repositories\DictionaryRepository $dictionaryRepo
     *
     * @deprecated 1.1.0 Used to support old API consumers
     *
     * @return mixed
     * @throws \Exception
     */
    public function getShipmentWithDriverAndDropdowns($id, DictionaryRepository $dictionaryRepo)
    {
        $shipment = $this->with([
            'products',
            'driverDetails',
            'shipmentPaymentMethod',
            'shipmentStatus',
            'shipmentReason',
        ])->findWhere(['id' => $id])->first();

        if (! $shipment) {
            throw new \Exception('Record cannot be found');
        }

        $return['pending_threshold']               = 3;
        $return['shipment_data']                   = $shipment->toArray();
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
            $data[$dataKey][$i]['status'] = $data[$dataKey][$i]['status']['value'];

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
}