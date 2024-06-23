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
        $date = $request->start_date ?? Carbon::today();
        return TripDates::with(['trip.salesman', 'tripTrace'])
            ->whereHas('tripTrace', function ($query) use ($request) {
                $query->whereNotNull('status');
            })
            ->whereDate('start_date', '=', $date)
            ->get()
            ->toArray();
    }

    public function updateTripTrace($request)
    {

        if($request->status == 'resume'){

            $trace = TripTrace::query()
                ->where('trip_date_id', '=', $request->trip_date_id)
                ->first();
            $trace->update(
                [
                    'status' => $request->status,
                ]
            );
            $delay = Carbon::make('0:0:0')->diffInMinutes($request->duration);
            $trace['tripDate']->increment('delay', $delay);
            return $trace;
        }

        $trace = TripTrace::query()
            ->where('trip_date_id', '=', $request->trip_date_id)
            ->first();
        $trace->update(
            [
                'duration' => $request->duration,
                'status' => $request->status,
            ]
        );
        return $trace;
    }


}
