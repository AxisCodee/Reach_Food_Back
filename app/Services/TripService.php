<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TripDates;
use App\Models\User;
use Carbon\Carbon;
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

    public function index($request)
    {
        return Trip::query()->where('branch_id',$request->branch_id)
        ->where('day',$request->day)
            ->with(['dates.order.customer','address','salesman'])
            ->get()->toArray();
    }

    public function createTrip($trip)
    {
        return DB::transaction(function () use ($trip) {

            $trip = Trip::query()->create([
                'address_id' => $trip['address_id'],
                'day' => $trip['day'],
                'branch_id' => $trip['branch_id'],
                'start_time' => $trip['start_time'],
            ]);
            $startDate = Carbon::parse(now())->next($trip['day']);
            TripDates::create([
                'trip_id' => $trip->id,
                'address_id' => $trip['address_id'],
                'start_time' => $trip['start_time'],
                'start_date' => $startDate->format('Y-m-d'),

            ]);
            return $trip;
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

    public function getSalesmanTrips()
    {
        $salesman = User::FindOrFail(auth('sanctum')->id());
        return $salesman->trips()
            ->with(['address:id,city_id,area', 'dates' => function($query) {
                $query->withCount('orders');
            }])
            ->get()
            ->toArray();
    }

    public function getSalesmanTripsWeekly()
    {
        $salesman = User::FindOrFail(4); //auth
        return $salesman->trips()
            ->with(['day:id,name', 'address:id,city_id,area'])
            ->get()
            ->toArray();
    }
}
