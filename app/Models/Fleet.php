<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Fleet extends Model{
    protected $fillable = [
        'name','owner','ships','position',
        'weaponA','weaponB','computer','power',
        'hull','PDamage','EDamage','shield','armor','evasion','speed','disengageChance',
        'created_at',
        'updated_at',
    ];

}
