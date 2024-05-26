<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->info('No branches found. Please seed cities first.');
            return;
        }

        $categories = [
            ['city_id' => $branches->random()->id, 'name' => 'Category 1'],
            ['city_id' => $branches->random()->id, 'name' => 'Category 2'],
            ['city_id' => $branches->random()->id, 'name' => 'Category 3'],
            ['city_id' => $branches->random()->id, 'name' => 'Category 4'],
            ['city_id' => $branches->random()->id, 'name' => 'Category 5'],
            // Add more addresses as needed
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
