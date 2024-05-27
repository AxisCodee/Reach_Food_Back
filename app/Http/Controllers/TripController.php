<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Day;
use App\Models\Trip;
use App\Models\User;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request)
    {
        $trips = $this->tripService->getTrips($request);
        return ResponseHelper::success($trips);
    }

    public function show(Request $request)
    {
        $trip = $this->tripService->getTrip($request);
        return ResponseHelper::success($trip);
    }

    public function store(CreateTripRequest $request)
    {
        $trip = $this->tripService->createTrip($request);
        return ResponseHelper::success($trip);
    }

    public function edit(UpdateTripRequest $request)
    {
        $result = $this->tripService->updateTrip($request);
        if ($result) {
            return ResponseHelper::success('Trip updated successfully');
        }
        return ResponseHelper::error('Failed to update trip');
    }

    public function delete(Request $request)
    {
        $result = $this->tripService->deleteTrip($request);
        if ($result) {
            return ResponseHelper::success('Trip deleted successfully');
        }
        return ResponseHelper::error('Failed to delete trip');
    }

    public function getDays()
    {
        $days = Day::all()->toArray();
        return ResponseHelper::success($days);
    }

    public function getSalesmanTrips()
    {
        $salesman = User::FindOrFail(auth('sanctum')->id());
//        $trips = $salesman->trips()->with(['day:id,name', 'address:id,city_id,area'])
//            ->withCount('orders')->get()->toArray();
        $trips = Trip::query()->where('address_id',$salesman->address_id)->get()->toArray();
        return ResponseHelper::success($trips);

    }

    public function getSalesmanTripsWeekly()
    {
        $trips = Day::query()->with(['trips.address'])
            ->get()->toArray();
        return ResponseHelper::success($trips);
    }
}
