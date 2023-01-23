<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class PlanetType extends Model {
    protected $fillable = [
        'name','localization','carryAble',
        'basePrefer',
        'created_at','updated_at'
    ];
}
