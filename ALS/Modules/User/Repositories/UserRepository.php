<?php

namespace ALS\Modules\User\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use ALS\Modules\Report\Repositories\ReportRepository;
use ALS\Modules\Shipment\Repositories\ShipmentRepository;
use ALS\Modules\User\Models\User;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }

    public function getDriverSummary($driverID, $isDriver = false)
    {
        $shipmentRepo   = $this->app->make(ShipmentRepository::class);
        $reportRepo     = $this->app->make(ReportRepository::class);
        $dictionaryRepo = $this->app->make(DictionaryRepository::class);

        $pendingStatus               = $dictionaryRepo->get('shipment_status', 'B')->first();
        $cancelledStatus             = $dictionaryRepo->get('shipment_status', 'C')->first();
        $deliveredStatus             = $dictionaryRepo->get('shipment_status', 'D')->first();
        $pickedStatus                = $dictionaryRepo->get('shipment_status', 'F')->first();
        $cashPaymentMethod           = $dictionaryRepo->get('pay_method', 19)->first();
        $cashOnDeliveryPaymentMethod = $dictionaryRepo->get('pay_method', 21)->first();

        $driverReport = $reportRepo->findOrCreate($driverID);

        $query = "report_id,
                  count(*)                                     AS total,
                  count(CASE WHEN status_id = {$pendingStatus->id}
                    THEN id END)                               AS pending,
                  count(CASE WHEN status_id = {$cancelledStatus->id}
                    THEN id END)                               AS cancelled,
                  count(CASE WHEN status_id = {$deliveredStatus->id}
                    THEN id END)                               AS delivered,
                  count(CASE WHEN status_id = {$pickedStatus->id}
                    THEN id END)                               AS picked,
                  count(CASE WHEN verified_by IS NOT NULL AND verified_by != 0
                    THEN id END)                               AS verified,
                  count(CASE WHEN type = 'pick'
                    THEN id END)                               AS pick_shipment_count,
                  count(CASE WHEN type = 'drop'
                    THEN id END)                               AS drop_shipment_count,
                  coalesce(sum(amount_order - amount_paid), 0) AS amount_order_to_collect,
                  coalesce(sum(amount_collected), 0)           AS amount_collected,
                  coalesce(sum(CASE WHEN pay_method = {$cashPaymentMethod->id}
                    THEN amount_collected END), 0)             AS cod,
                  coalesce(sum(CASE WHEN pay_method = {$cashOnDeliveryPaymentMethod->id}
                    THEN amount_collected END), 0)             AS ccod,
                  coalesce(sum(amount_order), 0)               AS order_amount";

        $where = ['report_id' => $driverReport->id, 'emp_driver_id' => $driverID];

        if ($isDriver) {
            $where[] = ['verified_by', '!=', 0];
            $where[] = ['verified_by', '!=', null];
        }

        $results = $shipmentRepo->rawWhere($query, $where);

        return $this->parserResult($results)->first();
    }
}