<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TechArea extends Model {
    protected $fillable = [
        'area','category',
        'created_at','updated_at'
    ];
}
