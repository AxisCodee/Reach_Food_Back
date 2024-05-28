<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create user details for each user
            DB::table('user_details')->insert([
                'user_id' => $user->id,
                'address_id' => Address::inRandomOrder()->first()->id, // or any other logic you prefer
                'image' => 'null', // or any default image path
                'location' => 'null', // or any default location
            ]);
        }
    }
}
