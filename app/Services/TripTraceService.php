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
        $tripTraces = TripDates::with(['trip.salesman', 'tripTrace'])
            ->whereDate('start_date', $request->start_date)
            ->get()->toArray();
        return $tripTraces;
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
