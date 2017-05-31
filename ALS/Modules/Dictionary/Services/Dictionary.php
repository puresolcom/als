<?php

namespace ALS\Modules\Dictionary\Services;

use ALS\Modules\Dictionary\Repositories\DictionaryRepository;

/**
 * Dictionary Service
 *
 * @package ALS\Modules\Dictionary\Services
 */
class Dictionary
{
    protected $dictionaryRepo;

    public function __construct(DictionaryRepository $dictionaryRepo)
    {
        $this->dictionaryRepo = $dictionaryRepo;
    }

    /**
     * Get Dictionary Records filtered by type and key
     *
     * @param string      $type
     * @param string|null $key
     *
     * @return mixed
     */
    public function get(string $type, string $key = null)
    {
        $where         = [];
        $where['type'] = $type;

        if (null !== $key) {
            $where['key'] = $key;
        }

        return $this->dictionaryRepo->findWhere($where);
    }
}