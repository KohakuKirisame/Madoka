<?php

namespace App\Models;

class Media extends \Illuminate\Database\Eloquent\Model {

    protected $fillable=[
        "id",
        "name",
        "country",
        "species",
        "created_at",
        "updated_at"
    ];

}
