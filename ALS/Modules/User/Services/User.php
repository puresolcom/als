<?php

namespace ALS\Modules\User\Services;

use ALS\Core\Eloquent\Model;
use ALS\Modules\Shipment\Repositories\ShipmentRepository;
use ALS\Modules\User\Repositories\UserRepository;
use Laravel\Lumen\Application;

/**
 * User Service
 *
 * Class User
 *
 * @package ALS\Modules\User\Services
 */
class User
{
    protected $app;

    protected $userRepo;

    public function __construct(Application $app, UserRepository $userRepo)
    {
        $this->app      = $app;
        $this->userRepo = $userRepo;
    }

    /**
     * Get user by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->userRepo->find($id, $columns);
    }

    /**
     * Get driver summary object
     *
     * @param int  $driverID
     * @param bool $isDriver
     *
     * @return Model|null
     */
    public function getDriverSummary($driverID, $isDriver = false)
    {
        $shipmentRepo      = $this->app->make(ShipmentRepository::class);
        $reportService     = $this->app->make('report');
        $dictionaryService = $this->app->make('dictionary');

        $pendingStatus               = $dictionaryService->get('shipment_status', 'B')->first();
        $cancelledStatus             = $dictionaryService->get('shipment_status', 'C')->first();
        $deliveredStatus             = $dictionaryService->get('shipment_status', 'D')->first();
        $pickedStatus                = $dictionaryService->get('shipment_status', 'F')->first();
        $cashPaymentMethod           = $dictionaryService->get('pay_method', 19)->first();
        $cashOnDeliveryPaymentMethod = $dictionaryService->get('pay_method', 21)->first();

        $driverReport = $reportService->findOrCreate($driverID);

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

        return $results->first();
    }
}