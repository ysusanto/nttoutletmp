<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankShop extends BaseModel
{
    //
    protected $table = 'bank_shop';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',
        'code_bank',
        'bank_name',
        'name',
        'account',
        'alias_name',
        'email',

    ];
}
