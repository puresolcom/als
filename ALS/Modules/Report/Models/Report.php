<?php

namespace ALS\Modules\Report\Models;

use ALS\Core\Eloquent\Model;

class Report extends Model
{
    protected $table = 'aw_report';

    protected $guarded = [];

    public $timestamps = false;
}