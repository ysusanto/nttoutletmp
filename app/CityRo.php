<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CityRo extends Model
{
    //
    protected $table = 'city_ros';

    protected $fillable = [
        'id_city_ro',
        'id_province_ro',
        'city',
        'type',
        'postal_code'
    ];
}
