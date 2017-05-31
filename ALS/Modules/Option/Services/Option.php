<?php

namespace ALS\Modules\Option\Services;

use ALS\Modules\Option\Repositories\OptionRepository;

/**
 * Option Service
 *
 * @package ALS\Modules\Option\Services
 */
class Option
{
    protected $optionRepo;

    public function __construct(OptionRepository $optionRepo)
    {
        $this->optionRepo = $optionRepo;
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
        $filter = ['module' => $module];
        if (! is_null($key)) {
            $filter['key'] = $key;
        }

        return $this->optionRepo->findWhere($filter)->first();
    }
}
