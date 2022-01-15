<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
class CityAirportSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $airportcitys = json_decode(file_get_contents(__DIR__ . '/data/airport_city.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\AirportCity::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($airportcitys as $city) {
            DB::table('airport_city')->insert([
                'icao_code' => isset($city['icao_code']) ? $city['icao_code'] :"",
                'iata_code' => isset($city['iata_code']) ? $city['iata_code'] :"",
                'airport_name' => isset($city['airport_name']) ? $city['airport_name'] : "",
                'City' => isset($city['City']) ? $city['City'] : "",
                'province' => isset($city['province']) ? $city['province'] : "",
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
            ]);
        }
    }
}
