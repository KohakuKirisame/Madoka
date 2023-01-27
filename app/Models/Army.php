<?php

namespace App\Models;

class Army extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'name','position','owner','damage','HP',
        'created_at','updated_at'
    ];
}

