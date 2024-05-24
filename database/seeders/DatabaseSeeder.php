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
            // Add other seeders here if necessary
        ]);

//        Branch::factory(10)->create();
//        User::factory(10)->create();
//        Category::factory(10)->create();
//        Product::factory()->count(10)->create();


        $this->call(CountrySeeder::class);
        $this->call(CitySeeder::class);
        $this->call(UserSeeder::class);
//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
    }
}
