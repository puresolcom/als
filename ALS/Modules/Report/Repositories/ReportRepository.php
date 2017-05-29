<?php
namespace ALS\Modules\Report\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Dictionary\Repositories\DictionaryRepository;
use Carbon\Carbon;

class ReportRepository extends BaseRepository
{
    public function model()
    {
        return \ALS\Modules\Report\Models\Report::class;
    }

    public function findOrCreate(int $driverID, $date = null, $create = false)
    {
        if (null == $date) {
            $date = Carbon::now()->toDateString();
        }

        // Find current report
        $report = $this->model->where('emp_driver_id', '=', $driverID)->whereDate('created_at', $date)->first();

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

            return $this->create([
                'keycode'       => $keyCode,
                'emp_driver_id' => $driverID,
                'created_at'    => $createdAt,
                'created_by'    => $createdBy,
                'status_id'     => $reportOpenStatus ? $reportOpenStatus->id : null,
            ]);
        }
    }
}