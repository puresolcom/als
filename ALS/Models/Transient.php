<?php

namespace ALS\Models;

use ALS\Core\Eloquent\Model;

class Transient Extends Model
{
    protected $table = 'aw_transient';

    protected $guarded = [];

    public $timestamps = false;
}