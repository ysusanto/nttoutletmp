<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankMidtrans extends BaseModel
{
    //
    protected $table = 'list_bank_midtrans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        

    ];
}
