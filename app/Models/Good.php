<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class Good extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'name','nameCN','basePrice',
        'created_at','updated_at'
    ];
}
