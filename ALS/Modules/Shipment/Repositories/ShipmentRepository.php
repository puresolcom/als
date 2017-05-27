<?php

namespace ALS\Modules\Shipment\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Shipment\Models\Shipment;

class ShipmentRepository extends BaseRepository
{
    public function model()
    {
        return Shipment::class;
    }
}