<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrderParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('order_parent', function (Blueprint $table) {
            $table->string('payment_token',255)->nullable();
            $table->string('payment_url',255)->nullable();
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
        Schema::table('order_parent', function (Blueprint $table) {
            $table->dropColumn('payment_token');
            $table->dropColumn('payment_url');
        });
       
    }
    
}
