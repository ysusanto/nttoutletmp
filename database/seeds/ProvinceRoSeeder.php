<?php

use Carbon\Carbon;

class ProvinceRoSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Get all of the timezones
        $provinces = json_decode(file_get_contents(__DIR__ . '/data/province_ro.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\province::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($provinces as $prov) {
            DB::table('provinces')->insert([
                'province_id_ro' => isset($prov['province_id_ro']) ? $prov['province_id_ro'] :null,
                'province' => isset($prov['province']) ? $prov['province'] : null,
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
                'province_eng'=>isset($prov['province_eng']) ? $prov['province_eng'] : null,
            ]);
        }
    }
}
