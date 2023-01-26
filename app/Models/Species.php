<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Species extends Model {
    protected $fillable = [
        'name','preference','needs','baseIntelligence','modifier',
        'created_at','updated_at'
    ];
}
