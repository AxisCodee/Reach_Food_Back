<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function store(OrderRequest $request)

    {

        $result = $this->orderService->storeOrder($request);
        return ResponseHelper::success($result, null, 'orders created successfully', 200);
    }

    public function assignOrder(OrderRequest $request)

    {
        $req=Request();
        $result = $this->orderService->assignOrder($request,auth('sanctum')->user()->id);
        return ResponseHelper::success($result, null, 'orders created successfully', 200);
    }



    public function update(Request $request, $order)
    {
        $result = $this->orderService->updateOrder($order,$request->customer_id);
        return ResponseHelper::success($result, null, 'orders update successfully', 200);
    }

    public function index()
    {
        $result = $this->orderService->indexOrder();
        return ResponseHelper::success($result, null, 'orders returned successfully', 200);
    }

    public function show($order)
    {
        $result = $this->orderService->showOrder($order);
        return ResponseHelper::success($result, null, 'orders returned successfully', 200);
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
