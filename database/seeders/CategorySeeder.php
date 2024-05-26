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
            $this->command->info('No branches found. Please seed branches first.');
            return;
        }

        $categories = [
            ['branch_id' => $branches->random()->id, 'name' => 'Category 1'],
            ['branch_id' => $branches->random()->id, 'name' => 'Category 2'],
            ['branch_id' => $branches->random()->id, 'name' => 'Category 3'],
            ['branch_id' => $branches->random()->id, 'name' => 'Category 4'],
            ['branch_id' => $branches->random()->id, 'name' => 'Category 5'],
            // Add more addresses as needed
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
