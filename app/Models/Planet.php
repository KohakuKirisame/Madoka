<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;

class Planet extends Model {
    protected $fillable = [
        'name','position','type','size','owner','controller','pops',
        'popGrowthProcess','districts','product','leaderParty',
        'created_at','updated_at'
    ];
}
