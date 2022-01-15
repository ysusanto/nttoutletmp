<?php

use Carbon\Carbon;

class ListBankMidtrans extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Get all of the bank
        $Banks = json_decode(file_get_contents(__DIR__ . '/data/list_bank_midtrans.json'), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\BankMidtrans::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($Banks as $bank) {
            DB::table('list_bank_midtrans')->insert([
                'code' => isset($bank['code']) ? $bank['code'] :"",
                'name' => isset($bank['name']) ? $bank['name'] : "",
                'created_at' => Carbon::Now(),
                'updated_at' => Carbon::Now(),
            ]);
        }
    }
}
