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
            return Trip::query()->create([
                'address_id' => $trip->address_id,
                'day_id' => $trip->day_id,
                'start_time' => $trip->start_time,
                'salesman_id' => $trip->salesman_id ?: auth('sanctum')->id(),
            ]);
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
