<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Job extends Model {
    protected $fillable = [
        'name','class','demand','supply',
        'created_at','updated_at'
    ];
}

