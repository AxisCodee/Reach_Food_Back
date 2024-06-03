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
        $data = $request->validated();
        $data['status'] = 'accepted';
        $data['order_date'] = Carbon::now()->format('Y-m-d');
        $data['customer_id'] = $customer_id;


        return DB::transaction(function () use ($req, $data, $request, $customer_id) {
            $total_price = 0;
            $price = 0;
            $result = Order::query()->create($data);

            $orderProducts = [];
            foreach ($req->product_id as $key => $product_id) {
                $product = Product::findOrFail($product_id);
                $orderProducts[] = [
                    'order_id'   => $result->id,
                    'product_id' => $product->id,
                    'quantity'   => $req->quantity[$key],
                ];
                $customer = User::findOrFail($customer_id);
                if ($customer->customer_type == 'shop') {
                    $price = $product->retail_price * $req->quantity[$key];
                }
                if ($customer->customer_type == 'center') {
                    $price = $product->wholesale_price * $req->quantity[$key];
                }
                $total_price += $price;

            }
            $customerAddress = User::where('id',$customer->id)->first();

            $trip = TripDates::query()
            ->where('address_id', $customerAddress->address_id)
            ->latest()->first();
                OrderProduct::insert($orderProducts);
                Order::where('id',$result->id)->update(['total_price' => $total_price, 'trip_date_id' => $trip->id]);

            return $result;
         });

    }

    public function updateOrder($order, $customer_id)
    {
        $total_price = 0;
        $price = 0;
        $req = Request();

        $orderProducts = [];
        return DB::transaction(function () use (&$price, $order, $req, $customer_id, &$total_price, &$orderProducts) {
            foreach ($req->product_id as $key => $product_id) {
                $product = Product::findOrFail($product_id);
                $orderProducts[] = [
                    'order_id' => $order,
                    'product_id' => $product->id,
                    'quantity' => $req->quantity[$key],
                ];

                $customer = User::findOrFail($customer_id);
                if ($customer->customer_type == 'shop') {
                    $price = $product->retail_price * $req->quantity[$key];
                }
                if ($customer->customer_type == 'center') {
                    $price = $product->wholesale_price * $req->quantity[$key];
                }
                $total_price += $price;
            }

            $orderId = OrderProduct::query();
            $orderId->where('order_id', $order)->delete();
            $orderId->insert($orderProducts);

            $order = Order::findOrFail($order);

            $customerAddress = UserDetail::where('user_id',$customer_id)->first();

            $trip = Trip::query()
            ->where('address_id', $customerAddress->address_id)
            ->latest()->first();

            $order->update([
                'customer_id' => $customer_id,
                'total_price' => $total_price,
                'trip_date_id' => $trip->id
            ]);

            return $order;
        });
    }



    public function indexOrder()
    {
        $category_id = request()->input('category_id');
        $result = Order::query()->where('category_id', $category_id)->paginate(10);
        return $result;
    }

    public function showOrder($order)
    {
        $result = Order::findOrFail($order);
        return $result;
    }

    public function deleteOrder($order)
    {
        $result = Order::findOrFail($order)->delete();
        return $result;
    }
}
