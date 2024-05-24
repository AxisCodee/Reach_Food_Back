<?php

namespace App\Services;

use App\Models\Trip;
use Illuminate\Support\Facades\DB;

/**
 * Class TripService.
 */
class TripService
{
    public function getTrips($request)
    {
        return Trip::query()
            ->with(['orders.customer'])
            ->where('day_id', $request->day_id)
            ->get();
    }

    public function getTrip($request)
    {
        return Trip::query()
            ->with(['salesman:id,name', 'orders:id,delivery_date.customer'])
            ->find($request->trip_id);
    }

    public function createTrip($trip)
    {
        return DB::transaction(function () use ($trip) {
            return Trip::query()->create($trip);
        });
    }

    public function updateTrip($request)
    {
        return DB::transaction(function () use ($request) {
            return Trip::query()->find($request->trip_id)->update($request);
        });
    }

    public function deleteTrip($request)
    {
        return DB::transaction(function () use ($request) {
            return Trip::query()->find($request->trip_id)->delete();
        });
    }

}
