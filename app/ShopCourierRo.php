<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopCourierRo extends Model
{
    //
      protected $table = 'shop_courier_ros';
         protected $fillable = [
                        'shop_id',
                        'id_courier',
                        'description',
                      
                    ];
}
