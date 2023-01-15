<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Market extends Model {
    protected $fillable = [
        'owner','member','planets','trades',
        'minerals','grain','consume_goods','alloys','gases','motes','crystals',
        'created_at','updated_at'
    ];
}
