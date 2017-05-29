<?php

namespace ALS\Modules\Dictionary\Models;

use ALS\Core\Eloquent\Model;

class Dictionary extends Model
{
    public $timestamps = false;

    protected $table = 'aw_dictionary';

    protected $guarded = [];
}