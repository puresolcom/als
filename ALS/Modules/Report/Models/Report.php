<?php

namespace ALS\Modules\Report\Models;

use ALS\Core\Eloquent\Model;
use ALS\Modules\Dictionary\Models\Dictionary;

class Report extends Model
{
    protected $table = 'aw_report';

    protected $guarded = [];

    public $timestamps = false;

    public function status()
    {
        return $this->hasOne(Dictionary::class, 'id', 'status_id');
    }
}