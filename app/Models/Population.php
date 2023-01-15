<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
class Population extends Model {
    protected $fillable = [
        'species','position','job','workat','ethic','ig','party','cash','struggle',
        'created_at','updated_at'
    ];
}
