<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class ShipType extends Model {
    protected $fillable = [
        'type','name','commandPoints','baseCost','buildTime',
        'baseHull','baseEvasion','baseSpeed','baseEDamage','basePDamage','baseArmor','baseShield','disengageChance',
        'created_at','updated_at'
    ];

}
