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
            'name' => 'Damascus',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'Damascus Countryside',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'Tartus',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'Tartus Countryside',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'Aleppo',
            'country_id' => 1,
        ]);
        City::create([
            'name' => 'Hama',
            'country_id' => 1,
        ]);
    }
}
