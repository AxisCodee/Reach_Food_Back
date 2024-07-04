<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\TripDates;
use Illuminate\Http\Request;

class TripDatesController extends Controller
{
    public function show(TripDates $tripDate)
    {
        return ResponseHelper::success($tripDate->order()->with(['customer.address', 'products'])->paginate(10));
    }
}
