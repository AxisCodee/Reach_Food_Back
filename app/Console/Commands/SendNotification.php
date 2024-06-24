<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TripDates;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notification';

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
        $trips = TripDates::query()
            ->whereDate('start_date', '=', Carbon::today())
            ->whereHas('tripTrace', function ($query) {
                $query->whereNull('status');
            })
            ->whereTime('start_time', '<=', Carbon::now()->toTimeString())
            ->with(['trip.salesman', 'trip.address.city'])
            ->get();
        foreach ($trips as $trip) {

        }
    }
}
