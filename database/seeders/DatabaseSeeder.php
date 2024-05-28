<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            AddressSeeder::class,
            DaySeeder::class,
            PermissionSeeder::class,
            BranchSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            UserDetailsSeeder::class
            // Add other seeders here if necessary
        ]);
    }
}
