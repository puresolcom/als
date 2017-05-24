<?php

namespace ALS\Models;

use Illuminate\Database\Eloquent\Model;

class Transient Extends Model
{
    protected $table = 'aw_transient';

    protected $guarded = [];

    public $timestamps = false;
}