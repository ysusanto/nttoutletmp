<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCartParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('cart_parent', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',50);
            $table->decimal('total', 20, 6)->nullable();
            $table->integer('payment_status')->default(1);
            $table->integer('payment_method_id')->unsigned();
            $table->integer('order_status_id')->unsigned()->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('cart_parent');
    }
}
