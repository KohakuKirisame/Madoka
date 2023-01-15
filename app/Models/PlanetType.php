<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class PlanetType extends Model {
    protected $fillable = [
        'name','localization','carryAble',
        'human','fox','deer','krik','robot',
        'created_at','updated_at'
    ];
}
