<?php

namespace App\Services;

use App\Models\CustomerTime;
use App\Models\Trip;
use App\Models\TripDates;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        return Trip::query()->where('branch_id', $request->branch_id)
            ->when($request->day, function (Builder $query) use ($request) {
                $query->where('day', $request->day);
            })
            ->with(['dates.order.customer', 'address', 'salesman'])
            ->get()->toArray();
    }

    public function createTrip($trip)
    {
        return DB::transaction(function () use ($trip) {
            $trips = Trip::create([
                'address_id' => $trip['address_id'],
                'day' => $trip['day'],
                'branch_id' => $trip['branch_id'],//??
                'start_time' => $trip['start_time'],
            ]);
            $startDate = Carbon::parse(now())->next($trip['day']);
             TripDates::create([
                'trip_id' => $trips->id,
                'address_id' => $trip['address_id'],
                'start_time' => $trip['start_time'],
                'start_date' => $startDate->format('Y-m-d'),
            ]);

            foreach ($trip->customerTimes as $customerId => $customerTime) {
                CustomerTime::create([
                    'customer_id' => $customerId,
                    'trip_id' => $trips->id,
                    'arrival_time' => $customerTime['time'],
                ]);
            }
            return $trips;
        });
    }

    public function updateTrip($request)
    {
        return DB::transaction(function () use ($request) {
            return Trip::query()->find($request->trip_id)->update($request);
        });
    }

    public function deleteTrip(Trip $trip)
    {
        $trip->delete();
    }

    public function getSalesmanTrips()
    {
        $salesman = User::FindOrFail(auth('sanctum')->id());
        return $salesman->trips()
            ->with(['address:id,city_id,area', 'dates' => function ($query) {
                $query->withCount('order');
            }])->paginate(10)
            ->get()

            ->toArray();//
    }

    public function getSalesmanTripsWeekly()
    {
        $salesman = User::FindOrFail(auth('sanctum')->id()); //auth
        return $salesman->trips()
            ->with(['address:id,city_id,area', 'dates'])
            ->get()
            ->toArray();
    }
}
