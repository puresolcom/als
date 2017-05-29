<?php

namespace ALS\Modules\Report\Models;

use ALS\Core\Eloquent\Model;
use ALS\Modules\Dictionary\Models\Dictionary;

class Report extends Model
{
    public $timestamps = false;

    protected $table = 'aw_report';

    protected $guarded = [];

    public function status()
    {
        return $this->hasOne(Dictionary::class, 'id', 'status_id');
    }
}