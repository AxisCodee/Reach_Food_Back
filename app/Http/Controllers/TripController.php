<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\NearTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\CustomerTime;
use App\Models\Trip;
use App\Models\User;
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

    public function nearTrip(NearTripRequest $request)
    {
        $data = $request->validated();
        try {
            $customer = User::query()
                ->where('role', Roles::CUSTOMER->value)
                ->findOrFail($data['customer_id']);

            $trip = $this->tripService->nearTrip($data['branch_id'], $customer['address_id']);
            $response['date'] = $trip['start_date'];
            $response['time'] = null;
            $customerTime = CustomerTime::query()
                ->where('customer_id', $customer['id'])
                ->where('trip_id', $trip->trip['id'])
                ->first();
            if($customerTime){
                $response['time'] = $customerTime['arrival_time'];
            }
            return ResponseHelper::success($response);
        } catch (Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }




    }

}
