<?php

namespace ALS\Modules\Dictionary\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Dictionary\Models\Dictionary;

class DictionaryRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Dictionary::class;
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

        return $this->findWhere($where);
    }
}