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
}