<?php
namespace ALS\Modules\Report\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Report\Models\Report;

class ReportRepository extends BaseRepository
{
    public function model()
    {
        return Report::class;
    }
}