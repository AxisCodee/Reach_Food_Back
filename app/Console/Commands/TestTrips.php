<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TripDates;
use App\Models\TripTrace;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-trips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trips = Trip::query()->get();
        foreach ($trips as $trip) {
            $trip_date = TripDates::query();
            $newTrip = $trip_date->create([
                'trip_id' => $trip->id,
                'start_time' => $trip->start_time,
                'start_date' => Carbon::now()->next($trip->day)->subDays(7)->format('Y-m-d'),
                'address_id' => $trip->address_id
            ]);
            TripTrace::query()
            ->create([
                'trip_date_id' => $newTrip->id,
            ]);
        }
    }
}
