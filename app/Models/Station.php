<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model {
    protected $fillable = [
        'position','name','type','owner','controller','modules','yardCapacity',
        'isTradeHub','buildings',
        'created_at','updated_at'
    ];
}
