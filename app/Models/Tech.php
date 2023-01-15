<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class Tech extends Model {
    protected $fillable = [
        'name','area','category','subject','cost',
        'is_rare','specialCountry','preTech','modifier',
        'created_at','updated_at'
    ];
}
