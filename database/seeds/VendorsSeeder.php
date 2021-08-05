<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class VendorsSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numner = 5;

        for ($i = 0; $i < $numner; $i++) {
            $email = $i == 0 ? 'merchant' : 'merchant' . $i;

            factory(App\Merchant::class, 1)
                ->create([
                    'shop_id' => $i + 1,
                    'email' => $email . '@demo.com',
                ])
                ->each(function ($merchant) {
                    $merchant->dashboard()->save(factory(App\Dashboard::class)->make());

                    $merchant->addresses()->save(
                        factory(App\Address::class)->make(['address_title' => $merchant->name, 'address_type' => 'Primary'])
                    );
                });
        }

        $this->call('ShopsSeeder');
    }
}
