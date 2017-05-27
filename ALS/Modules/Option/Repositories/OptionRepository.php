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

    /**
     * Fetch option by module and/or key
     *
     * @param $module
     * @param $key
     *
     * @return mixed
     */
    public function get($module, $key = null)
    {
        $filter = [ 'module' => $module ];
        if (!is_null($key)) {
            $filter[ 'key' ] = $key;
        }

        return $this->findWhere($filter)->first();
    }
}