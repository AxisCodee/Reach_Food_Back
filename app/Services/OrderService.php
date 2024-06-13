<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Trip;
use App\Models\TripDates;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderService.
 */
class OrderService
{

    public function storeOrder($request)
    {
        $data = $request->validated();
        $data['customer_id'] = Auth::user()->id;
        $result = Order::query()->create($data);
        return $result;
    }

    public function assignOrder($request, $customer_id)
    {
        $req = Request();

        //
        $data = $request->validated();
        $data['status'] = 'accepted';
        $data['order_date'] = Carbon::now()->format('Y-m-d');
        $data['customer_id'] = $customer_id;

        return DB::transaction(function () use ($req, $data, $request, $customer_id) {
            $totalPrice = 0;
            $order = Order::query()->create($data);
            $orderProducts = [];
            $customer = User::findOrFail($customer_id);
//dd($req->input('product_id'));
            foreach ($req->input('product_id') as $productData) {
//dd($productData);
                $product = Product::findOrFail($productData);
                $quantity = $productData['quantity'];

                $orderProducts[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ];

//                if ($customer->customer_type == 'shop') {
//                    $price = $product->retail_price * $quantity;
//                }
//                if ($customer->customer_type == 'center') {
//                    $price = $product->wholesale_price * $quantity;
//                }
                $price = ($customer->customer_type == 'shop' ? $product->retail_price : $product->wholesale_price) * $quantity;

                $totalPrice += $price;
            }

            OrderProduct::insert($orderProducts);

//            $customerAddress = User::where('id', $customer->id)->first();
            $trip = TripDates::query()
                ->where('address_id', $customer->address_id)
                ->latest()
                ->first();

//            if ($trip != null) {
//                Order::where('id', $result->id)
//                    ->update(['total_price' => $totalPrice, 'trip_date_id' => $trip->id]);
//            } else {
//                    Order::where('id', $result->id)
//                    ->update(['total_price' => $totalPrice, 'trip_date_id' => null]);
//            }

            $order->update([
                'total_price' => $totalPrice,
                'trip_date_id' => $trip?->id
            ]);

            return $order;
        });
    }


    public function updateOrder($request, $order, $customer_id)
    {
        $result = $this->assignOrder($request, $customer_id);

        $order = Order::where('id', $order)->first();

        if ($order->order_id == null) {
            $order->update(['order_id' => $result->id]);
        } else {
            $order->update(['order_id' => $result->order_id]);
        }

        Order::where('order_id', $order->id)->update(['order_id' => $result->id]);
        Order::where('id', $result->id)->update(['is_base' => 0]);

        return $result;
    }


    public function indexOrder(array $data)
    {
        return Order::query()
            ->with('trip_date.trip.salesman', 'customer.contacts', 'trip_date.address', 'childOrders')
            ->where('branch_id', $data['branch_id'])
            ->when($data['status'] ?? false, function (Builder $query) {
                $query->where('status', request()->status);
            })
            ->whereNull('order_id')
            ->when($data['is_archived'] ?? null, function (Builder $query) {
                $query->whereDate('order_date', '<', Carbon::now()->format('Y-m-d'));
            }, function (Builder $query) {
                $query->whereDate('order_date', '>=', Carbon::now()->format('Y-m-d'));
            });
    }

    public function showOrder($order)
    {
        $result = Order::whereNull('order_id')->with('products', 'customer.contacts', 'trip_date.trip.salesman', 'trip_date.trip.address', 'childOrders.products')->findOrFail($order);
        return $result;
    }

    public function deleteOrder($order)
    {
        $result = Order::findOrFail($order)->delete();
        return $result;
    }

    public function getSalesmanOrders($request)
    {
        $salesman = User::findOrFail(auth('sanctum')->id());//auth
        $customers = User::whereHas('trips.dates.order', function ($query) use ($salesman) {
            $query->where('salesman_id', $salesman->id);
        })
            ->with(['trips' => function ($query) use ($request) {
                $query->where('day', $request->input('day'));
            }])
            ->get()->toArray();
        return $customers;


    }


    private array $actions = [
        'cancel' => 'canceled',
        'deliver' => 'delivered'
    ];

    public function updateStatus($order, $action)
    {

        $status = $this->actions[$action];
        $order->update([
            'status' => $status,
            'delivery_date' => $status == 'delivered' ? Carbon::now()->toDate() : null,
            'delivery_time' => $status == 'delivered' ? Carbon::now()->toTimeString() : null,
        ]);
        return $order;
    }

}
