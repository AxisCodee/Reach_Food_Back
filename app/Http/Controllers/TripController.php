<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Services\TripService;
use Exception;
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
        $trips = $this->tripService->index($request);
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

    public function edit(UpdateTripRequest $request, $id)
    {
        $result = $this->tripService->updateTrip($request->validated(), Trip::query()->findOrFail($id));
        if ($result) {
            return ResponseHelper::success($result, '200');
        }
        return ResponseHelper::error('Failed to update trip');
    }

    public function destroy(Trip $trip)
    {
        try {
            $this->tripService->deleteTrip($trip);
            return ResponseHelper::success('Trip deleted successfully');
        } catch (Exception) {
            return ResponseHelper::error('Failed to delete trip');
        }
    }


    public function salesmanTripsDaily()
    {
        $trips = $this->tripService->getSalesmanTrips();
        return ResponseHelper::success($trips);

    }

    public function salesmanTripsWeekly()
    {
        $trips = $this->tripService->getSalesmanTripsWeekly();
        return ResponseHelper::success($trips);
    }

    public function restore($id)
    {
        $t = Trip::onlyTrashed()->where('id', $id)->first();
        if($t){
            if($this->tripService->conflicts($t)){
                return ResponseHelper::error('Failed to restore trip because conflicts');
            }
            $t->restore();
            return ResponseHelper::success($t, 200);
        }
        return ResponseHelper::error('Failed to restore trip');
    }

}
