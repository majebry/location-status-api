<?php

namespace App;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use SpatialTrait;

    protected $spatialFields = [
        'location'
    ];

    protected $fillable = [
        'location', 'pollution_rate'
    ];
}
