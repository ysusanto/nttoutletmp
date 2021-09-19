<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AirportCity extends Model
{
    //
    protected $table = 'airport_city';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao_code',
        'iata_code',
        'airport_name',
        'City',
        'province',
        

    ];
}
