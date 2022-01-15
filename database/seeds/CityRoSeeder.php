<?php

use Carbon\Carbon;

class CityRoSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Get all of the timezones
        $cities = json_decode(file_get_contents(__DIR__ . '/data/city_ro.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\CityRo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($cities as $city) {
            DB::table('city_ros')->insert([
                'id_city_ro' => isset($city['id_city_ro']) ? $city['id_city_ro'] :null,
                'id_province_ro' => isset($city['id_province_ro']) ? $city['id_province_ro'] :null,
                'city' => isset($city['city']) ? $city['city'] : null,
                'type' => isset($city['type']) ? $city['type'] : null,
                'postal_code' => isset($city['postal_code']) ? $city['postal_code'] : null,
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
            ]);
        }
    }
}
