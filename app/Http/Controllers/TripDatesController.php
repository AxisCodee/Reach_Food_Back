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
            ->when($request->input('s'), function (Builder $query, $search) {
                $query->whereHas('customer', function (Builder $query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%");
                });
            })
            ->with(['customer.address', 'products'])
            ->paginate(10);
        return ResponseHelper::success($orders);
    }
}
