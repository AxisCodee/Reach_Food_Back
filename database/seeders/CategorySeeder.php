<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            'name' => 'category1',
//            'branch_id' => 1, // replace with actual branch_id
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('categories')->insert([
            'name' => 'category2',
//            'branch_id' => 1, // replace with actual branch_id
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
