<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function trip_date(): BelongsTo
    {
        return $this->belongsTo(TripDates::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function childOrders()
    {
        return $this->hasMany(Order::class, 'order_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products', 'order_id', 'product_id');
    }

    public function scopeThisWeek(Builder $query): void
    {
        $startOfWeek = Carbon::now()->startOfWeek(CarbonInterface::SATURDAY);
        $endOfWeek = Carbon::now()->endOfWeek(CarbonInterface::FRIDAY);
        $query->whereBetween('delivery_date', [$startOfWeek, $endOfWeek]);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $query, $search) {
            $query->whereHas('customer', function (Builder $query) use ($search) {
                $query->where('name', 'LIKE', "%$search%");
            });
        });
    }

    public function scopeWithForSalesman(Builder $query): void
    {
        $query->with([
            'products',
            'customer' => [
                'contacts:id,user_id,phone_number',
                'address:id,city_id,area' => [
                    'city:id,name'
                ]
            ]
        ]);
    }

    public function scopeLastOrderAccepted(Builder $query, int $branchId, int $customerId): void
    {
        $query
            ->whereNull('order_id')
            ->where('branch_id', '=', $branchId)
            ->where('customer_id', '=', $customerId)
            ->where('delivery_date', '>=', Carbon::today()->toDateString())
            ->where('status', '=', 'accepted');
    }

    public function scopeArchived(Builder $query):void
    {
        $query->whereDate('order_date', '<', Carbon::now()->format('Y-m-d'));
    }
    public function scopeActive(Builder $query):void
    {
        $query->whereDate('order_date', '>=', Carbon::now()->format('Y-m-d'));
    }

    protected function canUndo(): Attribute
    {
        return Attribute::get(function () {
            if (
                Carbon::parse($this['delivery_date'])->lt(Carbon::today()->toDate()) ||
                Order::query()->lastOrderAccepted($this['branch_id'], $this['customer_id'])->exists()
            )
                return false;
            return true;
        });
    }

    protected function isLate(): Attribute
    {
        return Attribute::get(function () {
            if (Carbon::parse($this['delivery_time'])->lt(Carbon::now()->toDateTime()))
                return true;
            return false;
        });
    }

    protected function day(): Attribute
    {
        return Attribute::get(function () {
            return Carbon::parse($this['order_date'])->locale('ar')->dayName;
        });
    }

}
