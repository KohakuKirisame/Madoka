<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Situation extends Model {
    protected $fillable = [
        'country','title','description','process',
        'created_at','updated_at'
    ];
}

