<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class District extends Model {
    protected $fillable =[
        'name','buildCost','job','demandOrder','supplyOrder',
        'created_at','updated_at'
    ];
}
