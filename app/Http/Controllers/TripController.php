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
        return ResponseHelper::error('حدث خطأ في تحديث الرحلة');
    }

    public function destroy(Trip $trip)
    {
        $result = $this->tripService->deleteTrip($trip);
        if ($result)
            return ResponseHelper::success('Trip deleted successfully');

        return ResponseHelper::error('حدث خطأ في حذف الرحلة');

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
                return ResponseHelper::error('حدث خطأ في استعادة الرحلة بسبب تضارب الوقت');
            }
            $t->restore();
            return ResponseHelper::success($t, 200);
        }
        return ResponseHelper::error('حدث خطئ في استعادة الرحلة');
    }

}
