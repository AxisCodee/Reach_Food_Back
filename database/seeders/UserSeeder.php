<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\UsersPassword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'user_name' => 'super_admin01',
            'password' => Hash::make('password'),
            'role' => Roles::SUPER_ADMIN->value,
        ]);
//        DB::table('users')->insert([
//            'name' => 'Admin',
//            'user_name' => 'admin01',
//            'password' => Hash::make('password'),
//            'role' => Roles::ADMIN->value,
//        ]);
//        DB::table('users')->insert([
//            'name' => 'Sales Manager',
//            'user_name' => 'salesManager01',
//            'password' => Hash::make('password'),
//            'role' => Roles::SALES_MANAGER->value,
//        ]);
//        DB::table('users')->insert([
//            'name' => 'Salesman',
//            'user_name' => 'salesman01',
//            'password' => Hash::make('password'),
//            'role' => Roles::SALESMAN->value,
//        ]);
//        DB::table('users')->insert([
//            'name' => 'Customer',
//            'user_name' => 'customer01',
//            'password' => Hash::make('password'),
//            'role' => Roles::CUSTOMER->value,
//        ]);
//        DB::table('users')->insert([
//            'name' => 'Customer',
//            'user_name' => 'customer02',
//            'password' => Hash::make('password'),
//            'role' => Roles::CUSTOMER->value,
//        ]);
//        DB::table('users')->insert([
//            'name' => 'Salesman',
//            'user_name' => 'salesman02',
//            'password' => Hash::make('password'),
//            'role' => Roles::SALESMAN->value,
//        ]);
//        for ($i = 2; $i < 8; $i++) {
//            UsersPassword::create([
//                'user_id' => $i,
//                'password' => 'password',
//            ]);
//        }
    }
}
