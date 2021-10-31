<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderParent extends Model
{
    //
    protected $table = 'order_parent';

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
        'ip_address',
        'payment_url',
        'payment_token'
       
    ];
}
