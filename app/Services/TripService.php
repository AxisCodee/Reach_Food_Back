<?php

namespace App\Services;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
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
            ->with(['customers', 'address', 'salesman'])
            ->get()->toArray();
    }


    public function conflicts($trip): bool
    {
        return Trip::query()
            ->where('day', $trip['day'])
            ->where(function (Builder $q) use ($trip) {
                return $q
                    ->where(function (Builder $q) use ($trip) {
                        return $q
                            ->whereTime('start_time', '<=', $trip['start_time'])
                            ->WhereTime('end_time', '>', $trip['start_time']);
                    })
                    ->orWhere(function (Builder $q) use ($trip) {
                        return $q
                            ->whereTime('start_time', '<', $trip['end_time'])
                            ->WhereTime('end_time', '>=', $trip['end_time']);
                    });
            })
            ->where('salesman_id', $trip['salesman_id'])
            ->exists();
    }

    public function createTrip($trip)
    {
        return DB::transaction(function () use ($trip) {
            $conflicts = $this->conflicts($trip);

            if ($conflicts) {
                throw new \Exception('الاوقات متضاربة');
            }
            $trips = Trip::create([
                'address_id' => $trip['address_id'],
                'day' => $trip['day'],
                'branch_id' => $trip['branch_id'],//??
                'start_time' => $trip['start_time'],
                'end_time' => $trip['end_time'],
                'salesman_id' => $trip['salesman_id']
            ]);
            $startDate = Carbon::parse(now())->next($trip['day']);
            TripDates::create([
                'trip_id' => $trips->id,
                'address_id' => $trip['address_id'],
                'start_time' => $trip['start_time'],
                'start_date' => $startDate->format('Y-m-d'),
            ]);

            if (isset($trip['customerTimes'])) {
                foreach ($trip['customerTimes'] as $id => $customerTime) {
                    if ($customerTime['time'] < $trips['start_time']
                        || $customerTime['time'] > $trips['end_time']) {
                        throw new \Exception('وقت الزبون خاطئ');
                    }
                    CustomerTime::create([
                        'customer_id' => $id,
                        'trip_id' => $trips->id,
                        'arrival_time' => $customerTime['time'],
                    ]);
                }
            }
            return $trips;
        });
    }

    public function updateTrip($data, $trip)
    {
        return DB::transaction(function () use ($data, $trip) {
            $trip->delete();
            try {
                $new = $this->createTrip($data);
                $trip->forceDelete();
                return $new;
            } catch (\Exception $exception) {
                $trip->restore();
                throw $exception;
            }
        });
    }

    public function deleteTrip(Trip $trip): ?bool
    {
        $data = [
            'action_type' => NotificationActions::DELETE->value,
            'actionable_id' => $trip->id,
            'actionable_type' => Trip::class,
            'user_id' => auth()->id(),
        ];
        $ownerIds = GetUpperRoleUserIdsAction::handle(auth()->user());

        NotificationService::make($data, 0, $ownerIds);
        return $trip->delete();
    }

    public function getSalesmanTrips()
    {
        $salesman = User::findOrFail(auth('sanctum')->id());
        $date = Carbon::today();
        $isToday = true;
        if (request()->day) {
            $date = Carbon::now()->next(request()->day);
            $isToday = false;
        }
        $trips = $salesman->todayTripsDates()
            ->with(['trip','address:id,city_id,area'])
            ->withCount('order')
            ->whereDate('start_date', '=', $date)
            ->orderBy('start_time', 'asc')
            ->paginate(10)
            ->toArray();


        if ($isToday) {
            $newTrips = [];
            $tracingServices = new TripTraceService();
            $current = $tracingServices->currentTrip($salesman);
            $next = $tracingServices->next($salesman);
            foreach ($trips['data'] as $trip) {
                if ($current && $trip['id'] == $current['id']) {
                    $trip['status'] = 'current';
                } else if ($next && $trip['id'] == $next['id']) {
                    $trip['status'] = 'next';
                } else {
                    $trip['status'] = 'non';
                }
                $newTrips[] = $trip;
            }
            $trips['data'] = $newTrips;
        }

        return $trips;

    }

    public function getSalesmanTripsWeekly()
    {
        $salesman = User::FindOrFail(auth('sanctum')->id()); //auth
        return $salesman->trips()
            ->with(['address:id,city_id,area', 'dates'])
            ->paginate(10)
            ->toArray();
    }
}
