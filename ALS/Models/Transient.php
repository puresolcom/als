<?php

namespace ALS\Models;

use ALS\Core\Eloquent\Model;

class Transient Extends Model
{
    public $timestamps = false;

    protected $table = 'aw_transient';

    protected $guarded = [];
}