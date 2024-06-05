<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\OrderRequest;
use App\Services\DateService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $orderService, $dateService;

    public function __construct(OrderService $orderService, DateService $dateService)
    {
        $this->orderService = $orderService;
        $this->dateService = $dateService;

    }


    public function store(OrderRequest $request)
    {
        $req = Request();
        $result = $this->orderService->assignOrder($request, auth('sanctum')->user()->id);
        return ResponseHelper::success($result, null, 'orders created successfully', 200);
    }

    public function assignOrder(OrderRequest $request)
    {
        $req = Request();
        $result = $this->orderService->assignOrder($request, $request->customer_id);
        return ResponseHelper::success($result, null, 'orders created successfully', 200);
    }



    public function update(OrderRequest $request, $order)
    {
        $result = $this->orderService->updateOrder($request,$order,$request->customer_id);
        return ResponseHelper::success($result, null, 'orders update successfully', 200);
    }

    public function index(Request $request)
    {
        $date = $request->date;
        $result = $this->orderService->indexOrder();
        $data = $this->dateService->filterDate($result, $date, 'order_date');
        return ResponseHelper::success($data->paginate(10), null, 'orders returned successfully', 200);
    }

    public function show($order)
    {
        $result = $this->orderService->showOrder($order);
        return ResponseHelper::success($result, null, 'order returned successfully', 200);
    }

    public function destroy($order)
    {
        $result = $this->orderService->deleteOrder($order);
        return ResponseHelper::success($result, null, 'orders deleted successfully', 200);
    }

    public function confirm($order)
    {
        $result = $this->orderService->deleteOrder($order);
        return ResponseHelper::success($result, null, 'orders deleted successfully', 200);
    }



}
