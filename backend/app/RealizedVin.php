<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class RealizedVin extends Model
{
    use HasApiTokens;
    
    protected $fillable = [
        'vin', 'sdate', 'dealer_id'
    ];

    public function dealervin()
    {
        return $this->hasOne('App\Dealer', 'id', 'dealer_id');
    }
}
