<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCartParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('cart_parent', function (Blueprint $table) {
            //
            $table->bigInteger('customer_id')->nullable()->index();
            $table->string('ip_address',50)->nullable();
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
        Schema::table('cart_parent', function (Blueprint $table) {
            //
          
            $table->dropColumn('customer_id');
            $table->dropColumn('ip_address');
            // $table->dropColumn('payout_status');
           
        });
    }
}
