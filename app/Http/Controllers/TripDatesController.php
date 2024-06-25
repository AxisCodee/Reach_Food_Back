<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\TripDates;
use Illuminate\Http\Request;

class TripDatesController extends Controller
{
    public function show(TripDates $tripDates)
    {
        return ResponseHelper::success($tripDates->load(['order'=>[
            'customer',
            'products'
        ]]));
    }
}
