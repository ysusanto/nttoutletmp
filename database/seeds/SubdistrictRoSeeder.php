<?php

use Carbon\Carbon;

class SubdistrictRoSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Get all of the subdistricts
        $subdistricts = json_decode(file_get_contents(__DIR__ . '/data/subdistrict_ro.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\SubdistrictRo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($subdistricts as $subs) {
            DB::table('subdistrict_ros')->insert([
                'id_city_ro' => isset($subs['id_city_ro']) ? $subs['id_city_ro'] :null,
                'id_subdistrict_ro' => isset($subs['id_subdistrict_ro']) ? $subs['id_subdistrict_ro'] :null,
                'subdistrict' => isset($subs['subdistrict']) ? $subs['subdistrict'] : null,
                'type' => isset($subs['type']) ? $subs['type'] : null,
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
            ]);
        }
    }
}
