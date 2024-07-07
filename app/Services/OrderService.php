<?php

namespace App\Services;

use App\Actions\GetDaysNamesAction;
use App\Enums\NotificationActions;
use App\Enums\Roles;
use App\Events\SendMulticastNotification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Trip;
use App\Models\TripDates;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Exception;
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

        $data = $request->validated();
        $data['status'] = 'accepted';
        $data['order_date'] = Carbon::now()->format('Y-m-d');
        $data['customer_id'] = $customer_id;
        return DB::transaction(function () use ($req, $data, $request, $customer_id) {
            $order = Order::query()->create($data);
            $customer = User::findOrFail($customer_id);
            $data = $this->prepareProductsInOrder($request['product'], $order->id, $customer);
            OrderProduct::insert($data['order_products']);

            $trip = (new TripService())->nearTrip($customer->address_id, $req['branch_id']);

            if (!$trip) {
                throw new Exception('لا يمكن استقبال هذا الطلب لعدم وجود رحلة الى هذه المنطقة');
            }
            $order->update([
                'total_price' => $data['total_price'],
                'trip_date_id' => $trip->id
            ]);

            $order->load('trip_date.trip');
            return $order;
        });
    }

    private function prepareProductsInOrder($products, int $orderId, User $customer): array
    {
        $totalPrice = 0;
        $orderProducts = [];
        foreach ($products as $product) {
            $quantity = $product['quantity'];
            $product = Product::findOrFail($product['product_id']);

            $orderProducts[] = [
                'order_id' => $orderId,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ];

            $price = ($customer->customer_type == 'shop' ? $product->retail_price : $product->wholesale_price) * $quantity;

            $totalPrice += $price;
        }
        return [
            'order_products' => $orderProducts,
            'total_price' => $totalPrice,
        ];
    }

    private function customerUpdateOrder($request, Order $order): void
    {
        OrderProduct::query()
            ->where('order_id', $order->id)
            ->delete();

        $data = $this->prepareProductsInOrder($request['product'] ?? [], $order->id, auth()->user());
        DB::transaction(function () use ($order, $data) {
            OrderProduct::insert($data['order_products']);
            $order->update([
                'total_price' => $data['total_price'],
            ]);
        });
    }

    public function updateOrder($request, $order, $customer_id)
    {
        $order = Order::where('id', $order)->first();

        if (auth()->user()->role == Roles::CUSTOMER->value) {
            $this->customerUpdateOrder($request, $order);
            $result = $order;
        } else {
            $result = $this->assignOrder($request, $customer_id);

            if ($order->order_id == null) {
                $order->update(['order_id' => $result->id]);
            } else {
                $order->update(['order_id' => $result->order_id]);
            }

            Order::where('order_id', $order->id)->update(['order_id' => $result->id]);
            Order::where('id', $result->id)->update(['is_base' => 0]);
        }

        event(new SendMulticastNotification(
            auth()->id(),
            $this->getUserForNotification($order),
            NotificationActions::UPDATE->value,
            $order->branch_id,
            $order
        ));
        return $result;
    }


    public function indexOrder(array $data)
    {
        return Order::query()
            ->with(['customer.contacts', 'trip_date.address', 'childOrders', 'trip_date.trip.salesman'])
            ->when($data['products'] ?? false, function (Builder $query) {
                $query->with('products');
            })
            ->where('branch_id', $data['branch_id'])
            ->whereNull('order_id')
            ->when($data['is_archived'] ?? false,
                function (Builder $query) {
                    $query
                        ->whereDate('order_date', '<', Carbon::now()->format('Y-m-d'))
                        ->whereIn('status', ['delivered', 'canceled']);

                },
                function (Builder $query) {
                    $query
                        ->whereDate('order_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->whereIn('status', ['accepted', 'canceled']);
                })
            ->when($data['status'] ?? false, function (Builder $query) {
                $query->where('status', request()->status);
            });
    }

    public function showOrder($order)
    {
        $result = Order::whereNull('order_id')->with('products', 'customer.contacts', 'trip_date.trip.salesman', 'trip_date.trip.address', 'childOrders.products')->findOrFail($order);
        return $result;
    }

    public function deleteOrder($order)
    {
        $order = Order::findOrFail($order);
        event(new SendMulticastNotification(
            auth()->id(),
            $this->getUserForNotification($order),
            NotificationActions::DELETE->value,
            $order->branch_id,
            $order
        ));
        return $order->delete();
    }

    public function getSalesmanOrders($request)
    {
        return Order::query()
            ->whereHas('trip_date.trip', function ($query) use ($request) {
                $query
                    ->where('salesman_id', auth()->id())
                    ->when($request->input('days'), function ($query) use ($request) {
                        $query->whereIn('day', GetDaysNamesAction::handle($request->input('days')));
                    });
            })
            ->with([
                'products',
                'customer'
            ])
            ->get()
            ->each(function ($order) {
//                if($order->status== 'canceled')
                $order->setAppends(['can_undo']);
            })
            ->toArray();
//        $salesman = auth()->user();
//        $customers = User::whereHas('trips.dates.order', function ($query) use ($salesman) {
//            $query->where('salesman_id', $salesman->id);
//        })
//            ->with(['trips' => function ($query) use ($request) {
//                $query->when($request->input('days'), function ($query) use ($request) {
//                    $query->whereIn('day', GetDaysNamesAction::handle($request->input('days')));
//                });
//            }])
//            ->get()->toArray();
//        return $customers;
    }

    /**
     * @throws Exception
     */
    public function updateStatus($order, $data)
    {
        if (auth()->user()->role == Roles::CUSTOMER->value && $data['action'] != 'canceled')
            throw new Exception('لا يمكنك القيام بهذه العملية');
        $order->update([
            'status' => $data['action'],
            'delivery_date' => $data['delivery_date'] ?? $order['delivery_date'],
            'delivery_time' => $data['delivery_time'] ?? $order['delivery_time'],
        ]);
        if ($data['action'] == 'canceled') {
            /**
             * if the role is customer just send notification to salesman
             * else if the role is salesman send notification to customer and add notification to sales managers
             */
            $role = Roles::from(auth()->user()->role);
            switch ($role) {
                case Roles::SALESMAN:
                    $data = [
                        'action_type' => NotificationActions::CANCEL->value,
                        'actionable_id' => $order->id,
                        'actionable_type' => Order::class,
                        'user_id' => auth()->id(),
                    ];
                    $ownerIds = auth()
                        ->user()
                        ->salesManager()
                        ->where('users.branch_id', '=', $order->branch_id)
                        ->pluck('users.id')
                        ->toArray();
                    NotificationService::make($data, 0, $ownerIds);
                case Roles::CUSTOMER:
                    event(new SendMulticastNotification(
                        auth()->id(),
                        $this->getUserForNotification($order),
                        NotificationActions::CANCEL->value,
                        $order->branch_id,
                        $order
                    ));
                    break;
            }
        }
        return $order;
    }

    private function getUserForNotification(Order $order): array
    {
        return (auth()->user()->role == Roles::SALESMAN->value) ?
            [$order->customer_id] :
            [$order->trip_date->trip->salesman->id];
    }
}
