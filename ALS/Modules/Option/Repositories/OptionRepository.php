<?php

namespace ALS\Modules\Option\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Option\Models\Option;

class OptionRepository extends BaseRepository
{
    public function model()
    {
        return Option::class;
    }
}