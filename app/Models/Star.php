<?php

namespace app\Models;

class Star extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'name','x','y','type',
        'owner','controller','hyperlane','havePlanet','stationType',
        'created_at','updated_at'
    ];

}
