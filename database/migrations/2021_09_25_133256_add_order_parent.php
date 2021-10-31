<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('order_parent', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',50);
            $table->decimal('total', 20, 6)->nullable();
            $table->integer('payment_status')->default(1);
            $table->integer('payment_method_id')->unsigned();
            $table->integer('order_status_id')->unsigned()->default(1);
            $table->bigInteger('customer_id')->nullable()->index();
            $table->string('ip_address',50)->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->bigInteger('op_id')->nullable()->index();
            // $table->string('payout_status')->nullable();
            
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
        Schema::dropIfExists('order_parent');
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('op_id');
            // $table->dropColumn('payout_status');
           
        });
    }
}
