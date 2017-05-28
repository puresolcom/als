<?php

namespace ALS\Modules\Location\Models;

use ALS\Core\Eloquent\Model;

class Location extends Model
{
    public $timestamps = false;

    protected $table = 'aw_location';

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id', 'id');
    }

    public function recursiveParents()
    {
        return $this->parent()->with('recursiveParents');
    }
}