<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartParent extends Model
{
    //
    protected $table = 'cart_parent';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
  

     */
    protected $fillable = [
        'code',
        'total',
        'payment_status',
        'payment_method_id',
        'order_status_id',
        'customer_id',
        'ip_address'
       
    ];
}
