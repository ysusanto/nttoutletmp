<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class InventoriesSeeder extends BaseSeeder
{

    private $itemCount = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Inventory::class, $this->itemCount)->create();

        // Seed some intems for under $99 section
        for ($i = 0; $i < 6; $i++) {
            $sale_price = rand(5, 97);
            $offer_price = rand(1, 0) ? $sale_price - rand(1, $sale_price - 3) : Null;

            factory(App\Inventory::class)->create([
                'sale_price' => $sale_price,
                'offer_price' => $offer_price,
                'offer_start' => $offer_price ? Carbon::Now()->format('Y-m-d h:i a') : Null,
                'offer_end' => $offer_price ? date('Y-m-d h:i a', strtotime(rand(3, 22) . ' days')) : Null,
            ]);
        }

        if (File::isDirectory($this->demo_dir)) {
            $data = [];
            $inventories = \DB::table('inventories')->pluck('id')->toArray();
            $img_dirs = glob($this->demo_dir . '/products/*', GLOB_ONLYDIR);

            foreach ($inventories as $item) {
                $images = glob($img_dirs[array_rand($img_dirs)] . DIRECTORY_SEPARATOR . '*.{jpg,png,jpeg}', GLOB_BRACE);

                foreach ($images as $file) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $name = Str::random(10) . '.' . $ext;
                    $targetFile = $this->dir . DIRECTORY_SEPARATOR . $name;

                    if ($this->disk->put($targetFile, file_get_contents($file))) {
                        $data[] = [
                            'name' => $name,
                            'path' => $targetFile,
                            'extension' => $ext,
                            'size' => filesize($file),
                            'imageable_id' => $item,
                            'imageable_type' => 'App\Inventory',
                            'created_at' => Carbon::Now(),
                            'updated_at' => Carbon::Now(),
                        ];
                    }
                }
            }

            \DB::table('images')->insert($data);
        }
    }
}
