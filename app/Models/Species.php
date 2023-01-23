<?php

namespace App\Models;

class Species extends Model {
    protected $fillable = [
        'name','preference','needs','baseIntelligence','modifier',
        'created_at','updated_at'
    ];
}
