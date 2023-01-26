<?php

namespace App\Models;

class News extends \Illuminate\Database\Eloquent\Model {

    protected $fillable=[
        "id",
        "title",
        "content",
        "editor",
        "media",
        "type",
        "status",
        "created_at",
        "updated_at"
    ];

}
