<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\TripDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TripDatesController extends Controller
{
    public function show(Request $request, TripDates $tripDate)
    {
        $orders = $tripDate
            ->order()
            ->search($request->input('s'))
            ->withForSalesman()
            ->paginate(10);
        return ResponseHelper::success($orders);
    }
}
