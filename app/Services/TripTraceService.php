<?php

namespace App\Services;

use App\Models\TripDates;
use App\Models\TripTrace;
use Illuminate\Support\Carbon;

/**
 * Class TripTraceService.
 */
class TripTraceService
{
    public function getTripTraces($request)
    {
        $time = $request->start_date ?? Carbon::now()->format('Y-m-d');
        return TripDates::with(['trip.salesman'])
            ->whereHas('tripTrace', function ($query) use ($request) {
                $query->whereNotNull('status');
            })
            ->whereDate('start_date', $time)
            ->get()
            ->toArray();
    }

    public function updateTripTrace($request)
    {
        return TripTrace::updateOrCreate(
            ['trip_date_id' => $request->trip_date_id],
            [
                'duration' => $request->duration,
                'status' => $request->status,
            ]
        );

    }


}
