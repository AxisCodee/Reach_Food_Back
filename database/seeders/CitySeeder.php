<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::create([
            'name' => 'دمشق',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'ريف دمشق',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'طرطوس',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'حلب',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'حمص',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'حماه',
            'country_id' => 1,
        ]);
    }
}
