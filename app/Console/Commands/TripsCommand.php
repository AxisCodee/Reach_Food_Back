<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TripDates;
use App\Models\TripTrace;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TripsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:trips';

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

        if ($trips) {
            foreach ($trips as $trip) {
                if ($trip->day === Carbon::now()->format('l')) {

                    $trip_date = TripDates::query();

                    $newTrip = $trip_date->create([
                        'trip_id' => $trip->id,
                        'start_time' => $trip->start_time,
                        'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                        'address_id' => $trip->address_id
                    ]);
                    TripTrace::query()
                        ->create([
                            'trip_date_id' => $newTrip->id,
                        ]);
                }
            }
        }
    }
}
