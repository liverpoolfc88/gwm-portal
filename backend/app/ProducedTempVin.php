<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProducedTempVin extends Model
{
    protected $fillable = [
        'skd_plant',
        'vin_gm' ,
        'vin_local',
        'model_code',
        'model_year',
        'engine',
        'full_option' ,
        'produced_date',
        'to_dealer' ,
        'sold_date' ,
        'user_id' ,
    ];
}
