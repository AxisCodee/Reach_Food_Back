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
        $trips = Trip::query()
            ->where('day', 'Monday')
            ->where('salesman_id', 7)
            ->get();
        foreach ($trips as $trip){
            Trip::query()->create([
                'salesman_id' => $trip->salesman_id,
                'day' => Carbon::today()->dayName,
                'address_id' => $trip->address_id,
                'branch_id' => $trip->branch_id,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
            ]);
        }
        $trips = Trip::query()
            ->where('day', Carbon::today()->dayName)
            ->where('salesman_id', 7)
            ->get();
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
