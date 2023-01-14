<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ShipComputer extends Model {
    protected $fillable =[
        'localization','selfModifier','computerAnti',
        'created_at','updated_at'
    ];
}
