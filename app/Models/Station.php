<?php

namespace App\Models;

class Station extends Models {
    protected $fillable = [
        'position','name','type','owner','controller','modules','yardCapacity',
        'isTradeHub','buildings',
        'created_at','updated_at'
    ];
}
