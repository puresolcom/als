<?php

namespace ALS\Modules\Dictionary\Models;

use ALS\Core\Eloquent\Model;

class Dictionary extends Model
{
    protected $table = 'aw_dictionary';

    protected $guarded = [];

    public $timestamps = false;
}