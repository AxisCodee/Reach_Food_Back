<?php

namespace App\Services;

use App\Models\TripDates;
use App\Models\TripTrace;

/**
 * Class TripTraceService.
 */
class TripTraceService
{
    public function getTripTraces($request)
    {
        return TripDates::with(['trip.salesman'])
            ->whereHas('tripTrace', function ($query) use ($request) {
                $query->where([]);
            })
            ->whereDate('start_date', $request->start_date)
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
