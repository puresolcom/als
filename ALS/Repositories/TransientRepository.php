<?php

namespace ALS\Repositories;

use ALS\Models\Transient;
use Prettus\Repository\Eloquent\BaseRepository;

class TransientRepository extends BaseRepository
{
    public function model()
    {
        return Transient::class;
    }
}