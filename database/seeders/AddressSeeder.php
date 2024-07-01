<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure you have some cities created before creating addresses
//        $cities = City::all();
//
//        if ($cities->isEmpty()) {
//            $this->command->info('No cities found. Please seed cities first.');
//            return;
//        }

        $addresses = [
            ['city_id' => 1, 'area' => 'منطقة 1'],
            ['city_id' => 2, 'area' => 'منطقة 2'],
            ['city_id' => 3, 'area' => 'منطقة 3'],
            ['city_id' => 4, 'area' => 'منطقة 4'],
            ['city_id' => 1, 'area' => 'منطقة 5'],
            // Add more addresses as needed
        ];

        foreach ($addresses as $address) {
            Address::create($address);
        }
    }
}
