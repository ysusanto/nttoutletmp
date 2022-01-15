<?php

use Carbon\Carbon;

class CourierRoSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Get all of the Couriers
        $Couriers = json_decode(file_get_contents(__DIR__ . '/data/courier_ro.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\CourierRo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($Couriers as $kurir) {
            DB::table('courier_ros')->insert([
                'parent_id' => isset($kurir['parent_id']) ? $kurir['parent_id'] :null,
                'code' => isset($kurir['code']) ? $kurir['code'] :"",
                'name' => isset($kurir['name']) ? $kurir['name'] : "",
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
            ]);
        }
    }
}
