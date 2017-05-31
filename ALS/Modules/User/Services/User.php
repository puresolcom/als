<?php

namespace ALS\Modules\User\Services;

use ALS\Modules\Expense\Services\Expense;
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
     * @return array|null
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

        $shipmentSummaryResult = $shipmentRepo->rawWhere($query, $where)->first()->toArray();
        $expensesSummary       = $this->getExpensesSummary($driverReport->id);

        return $this->formatSummary(array_merge($shipmentSummaryResult, $expensesSummary));
    }

    /**
     * Get Expenses Summary
     *
     * @param $driverReportID
     *
     * @return array
     */
    protected function getExpensesSummary($driverReportID)
    {
        /** @var Expense $expenseService */
        $expenseService = $this->app->make('expense');
        $reportExpenses = $expenseService->getByReportID($driverReportID)->toArray();

        $totalExpenses    = 0;
        $totalExpensesSum = number_format(0, 2, '.', '');

        if ($reportExpenses) {
            $totalExpenses    = count($reportExpenses);
            $totalExpensesSum = number_format(array_sum(array_column($reportExpenses, 'amount')), 2, '.', '');
        }

        return [
            'total_expenses_count' => $totalExpenses,
            'total_expenses_sum'   => $totalExpensesSum,
        ];
    }

    protected function formatSummary($result)
    {
        if (! $result) {
            return null;
        }

        return [
            'active'               => $result['total'],
            'delivered'            => $result['delivered'],
            'verified'             => $result['verified'],
            'picked'               => $result['picked'],
            'pending'              => ( string ) ($result['total'] - ($result['delivered'] + $result['picked'] + $result['cancelled'])),
            'cancelled'            => $result['cancelled'],
            'total_pick'           => $result['pick_shipment_count'],
            'total_drop'           => $result['drop_shipment_count'],
            'addressed_orders'     => ( string ) ($result['delivered'] + $result['picked'] + $result['cancelled']),
            'newly_assigned'       => ( string ) ($result['total'] - ($result['delivered'] + $result['picked'] + $result['pending'] + $result['cancelled'])),
            'sum_collected'        => [
                'total' => $result['amount_collected'],
                'CAOD'  => $result['cod'],
                'CCOD'  => $result['ccod'],
            ],
            'collection_amount'    => number_format($result['amount_order_to_collect'], 2, '.', ''),
            'total_order_sum'      => number_format($result['order_amount'], 2, '.', ''),
            'total_expenses_count' => $result['total_expenses_count'],
            'total_expenses_sum'   => $result['total_expenses_sum'],
            'deposit_amount'       => number_format((number_format($result['cod'], 2, '.', '') - $result['total_expenses_sum']), 2, '.', ''),
        ];
    }
}