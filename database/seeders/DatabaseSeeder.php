<?php

namespace Database\Seeders;

use App\Enums\NotificationActions;
use App\Events\SendMulticastNotification;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

//        event(new SendMulticastNotification(9,[4],NotificationActions::UPDATE->value,Order::find(1)));
//        $this->call([
//            CountrySeeder::class,
//            CitySeeder::class,
//            AddressSeeder::class,
//            PermissionSeeder::class,
//            BranchSeeder::class,
//            UserSeeder::class,
//            // Add other seeders here if necessary
//        ]);
    }
}
