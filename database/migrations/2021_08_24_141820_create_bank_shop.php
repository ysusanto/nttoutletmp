<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankShop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_shop', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->string('code_bank', 50);
            $table->string('bank_name', 255);
            $table->string('name', 255);
            $table->string('account', 50);
            $table->string('alias_name', 50);
            $table->string('email', 50);
            $table->timestamps();
            //  $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_shop');
    }
}
