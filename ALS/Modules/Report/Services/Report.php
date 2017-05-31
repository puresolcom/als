<?php

namespace ALS\Modules\Report\Services;

use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use ALS\Modules\Report\Repositories\ReportRepository;
use Carbon\Carbon;
use Laravel\Lumen\Application;

/**
 * Report Service
 *
 * @package ALS\Modules\Report\Services
 */
class Report
{
    protected $app;

    protected $reportRepo;

    public function __construct(Application $app, ReportRepository $reportRepo)
    {
        $this->app        = $app;
        $this->reportRepo = $reportRepo;
    }

    /**
     * Find driver report for specific date or create new if non-existent
     *
     * @param int  $driverID
     * @param null $date today datetime will be used
     * @param bool $create
     *
     * @return bool
     */
    public function findOrCreate(int $driverID, $date = null, $create = false)
    {
        if (null == $date) {
            $date = Carbon::now()->toDateString();
        }

        // Find current report
        $report = $this->reportRepo->makeModel()->where('emp_driver_id', '=', $driverID)->whereDate('created_at', $date)->first();

        if ($report) {
            return $report;
        } elseif (! $create) {
            return false;
        } else {
            // Create new one
            $dictionaryRepo = $this->app->make(DictionaryRepository::class);

            $reportOpenStatus = $dictionaryRepo->get('report_status', 'open')->first();

            $keyCode = Carbon::now()->getTimestamp().$driverID;

            $createdAt = Carbon::now()->format('Y-m-d H:i:s');

            $createdBy = $this->app->make('auth')->user()->id;

            return $this->reportRepo->create([
                'keycode'       => $keyCode,
                'emp_driver_id' => $driverID,
                'created_at'    => $createdAt,
                'created_by'    => $createdBy,
                'status_id'     => $reportOpenStatus ? $reportOpenStatus->id : null,
            ]);
        }
    }
}