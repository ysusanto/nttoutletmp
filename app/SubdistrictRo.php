<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubdistrictRo extends Model
{
    //
    protected $table = 'subdistrict_ros';
    protected $fillable = [
        'id_subdistrict_ro',
        'id_city_ro',
        'subdistrict',
        'type'
    ];
}
