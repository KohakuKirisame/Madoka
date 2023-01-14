<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class Ship extends Model {
    protected $fillable =[
        'name','owner','shipType',
        'created_at','updated_at'
    ];
}
