<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class province extends Model
{
    //
    protected $table = 'provinces';

    protected $fillable = [
        'province_id_ro',
        'province',
        'province_eng'
    ];
}
