<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierRo extends Model
{
    //
    protected $table = 'courier_ros';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'path_logo'
    ];
}
