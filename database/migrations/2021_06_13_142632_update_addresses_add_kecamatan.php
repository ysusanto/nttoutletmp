<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAddressesAddKecamatan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('addresses', function (Blueprint $table) {
            //
            $table->string('subdistrict_1',100)->nullable()->comment('kecamatan');
            $table->string('subdistrict_2',100)->nullable()->comment('kelurahan');
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
        Schema::table('addresses', function (Blueprint $table) {
            //
            $table->dropColumn('subdistrict_1');
            $table->dropColumn('subdistrict_2');
        });
    }
}
