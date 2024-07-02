<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UpdateOrCreateTraceRequest;
use App\Models\User;
use App\Services\TripTraceService;
use Illuminate\Http\Request;

class TripTraceController extends Controller
{
    protected $tripTraceService;

    public function __construct(TripTraceService $tripTraceService)
    {
        $this->tripTraceService = $tripTraceService;
    }

    public function index(Request $request)//Dashboard
    {
        $result = $this->tripTraceService->getTripTraces($request);
        return ResponseHelper::success($result);
    }

    public function show()
    {
        //
    }

    public function updateOrCreate(UpdateOrCreateTraceRequest $request)
    {
        $result = $this->tripTraceService->updateTripTrace($request);
        return ResponseHelper::success($result);
    }


    public function tracing($action)
    {
        $user = auth()->user();//todo add auth id
        $functionName = $action . 'Trip';
        try {
           $this->tripTraceService->$functionName($user);
        }catch (\Exception $exception){
            return ResponseHelper::error($exception->getMessage());
        }
        return ResponseHelper::success('updated data');
    }

    public function destroy()
    {
        //
    }

}
