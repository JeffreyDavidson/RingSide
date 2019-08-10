<?php

use App\Models\Wrestler;
use Illuminate\Database\Seeder;

class WrestlersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($w = 1; $w <= 50; $w++) {
            factory(Wrestler::class)->create([
                'name' => 'Wrestler '.$w,
            ])->employments()->create([
                'started_at' => now()->subYear(1)
            ]);
        }

        $eNum = 51;
        for ($i = 1; $i <= 12; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                factory(Wrestler::class)->create([
                    'name' => 'Wrestler '. $eNum,
                ])->employments()->create([
                    'started_at' => now()->subYear(1)->addMonth($i)
                ]);
                $eNum ++;
            }
        }
    }
}
