<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Definition extends Model{
    protected $fillable =[
        'name','localization','area','economyKey','modifierKey',
        'created_at','updated_at',
    ];
}
