<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Requests\Order\UpdateArchivedOrderRequest;
use App\Models\Order;
use App\Services\DateService;
use App\Services\OrderService;
use Illuminate\Http\Request;

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
        $result = $this->orderService->updateOrder($request, $order, $request->customer_id);
        return ResponseHelper::success($result, null, 'orders update successfully', 200);
    }

    public function index(IndexOrderRequest $request)
    {
        $data = $request->validated();
        $result = $this->orderService->indexOrder($data);
        $data = $this->dateService->filterDate($result, $data['date'] ?? false, 'order_date');
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

//salesman

    public function salesmanOrders(Request $request)
    {
        $result = $this->orderService->getSalesmanOrders($request);
        return ResponseHelper::success($result);
    }


    public function updateStatus(UpdateArchivedOrderRequest $request, $id){
        $order = Order::query()->findOrFail($id);
        return ResponseHelper::success(
            $this->orderService->updateStatus($order, $request->validated())
        );
    }
}
