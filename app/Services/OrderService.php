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
            $price = 0;

            $result = Order::query()->create($data);
            $orderProducts = [];

            foreach ($req->input('product') as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantity = $productData['quantity'];

                $orderProducts[] = [
                    'order_id' => $result->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ];

                $customer = User::findOrFail($customer_id);
                if ($customer->customer_type == 'shop') {
                    $price = $product->retail_price * $quantity;
                }
                if ($customer->customer_type == 'center') {
                    $price = $product->wholesale_price * $quantity;
                }

                $totalPrice += $price;
            }

            OrderProduct::insert($orderProducts);

            $customerAddress = User::where('id', $customer->id)->first();
            $trip = TripDates::query()
                ->where('address_id', $customerAddress->address_id)
                ->latest()
                ->first();

            if ($trip != null) {
                Order::where('id', $result->id)
                    ->update(['total_price' => $totalPrice, 'trip_date_id' => $trip->id]);
            } else {
                    Order::where('id', $result->id)
                    ->update(['total_price' => $totalPrice, 'trip_date_id' => null]);
            }

            return $result;
        });
    }


    public function updateOrder($request, $order, $customer_id)
    {
        $result = $this->assignOrder($request, $customer_id);
        // dd($result->id);

        $order = Order::where('id', $order)->first();
        //  dd( $result->id);

        if ($order->order_id == null) {
            $order->update(['order_id' => $result->id]);
        } else {
            $order->update(['order_id' => $result->order_id]);
        }

        Order::where('order_id', $order->id)->update(['order_id' => $result->id]);
        Order::where('id', $result->id)->update(['is_base' => 0]);

        return $result;


        // $total_price = 0;
        // $price = 0;
        // $req = Request();

        // $orderProducts = [];
        // return DB::transaction(function () use (&$price, $order, $req, $customer_id, &$total_price, &$orderProducts) {
        //     foreach ($req->product_id as $key => $product_id) {
        //         $product = Product::findOrFail($product_id);
        //         $orderProducts[] = [
        //             'order_id' => $order,
        //             'product_id' => $product->id,
        //             'quantity' => $req->quantity[$key],
        //         ];

        //         $customer = User::findOrFail($customer_id);
        //         if ($customer->customer_type == 'shop') {
        //             $price = $product->retail_price * $req->quantity[$key];
        //         }
        //         if ($customer->customer_type == 'center') {
        //             $price = $product->wholesale_price * $req->quantity[$key];
        //         }
        //         $total_price += $price;
        //     }

        //     $orderId = OrderProduct::query();
        //     $orderId->where('order_id', $order)->delete();
        //     $orderId->insert($orderProducts);

        //     $order = Order::findOrFail($order);

        //     $customerAddress = UserDetail::where('user_id', $customer_id)->first();

        //     $trip = Trip::query()
        //         ->where('address_id', $customerAddress->address_id)
        //         ->latest()->first();

        //     $order->update([
        //         'customer_id' => $customer_id,
        //         'total_price' => $total_price,
        //         'trip_date_id' => $trip->id
        //     ]);

        //     return $order;
        // });
    }


    public function indexOrder()
    {
        $branch_id = request()->branch_id;
        $status = request()->status;

        if ($status) {
            $result = Order::query()->with('trip_date.trip.salesman', 'customer.contacts', 'trip_date.address', 'childOrders')
                ->where('branch_id', $branch_id)->where('status', $status)->whereNull('order_id');
        } else {
            $result = Order::query()->with('trip_date.trip.salesman', 'customer.contacts', 'trip_date.address', 'childOrders')
                ->where('branch_id', $branch_id)->whereNull('order_id');
        }
        return $result;
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



}
