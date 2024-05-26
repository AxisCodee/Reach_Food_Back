<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\City;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all();

        if ($cities->isEmpty()) {
            $this->command->info('No cities found. Please seed cities first.');
            return;
        }

        $branches = [
            ['city_id' => $cities->random()->id, 'name' => 'Branch 1'],
            ['city_id' => $cities->random()->id, 'name' => 'Branch 2'],
            ['city_id' => $cities->random()->id, 'name' => 'Branch 3'],
            ['city_id' => $cities->random()->id, 'name' => 'Branch 4'],
            ['city_id' => $cities->random()->id, 'name' => 'Branch 5'],
            // Add more addresses as needed
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
