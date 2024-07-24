<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
//    use HasFactory;

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function customerWithoutTrashed(): BelongsTo
    {
        return $this->belongsTo(User::class,'customer_id');
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
        return $this->belongsToMany(Product::class, 'order_products', 'order_id', 'product_id')->withPivot('quantity');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'actionable');
    }

    public function scopeNextWeek(Builder $query): void
    {
        $date = Carbon::today();
        $startOfWeek = $date->toDateString();
        $endOfWeek = $date->addDays(6)->toDateString();
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


    public function scopeNotArchiveOrder(Builder $query, int $branchId, int $customerId): void
    {
        $query
            ->whereNull('order_id')
            ->where('branch_id', '=', $branchId)
            ->where('customer_id', '=', $customerId)
            ->where('delivery_date', '>=', Carbon::today()->toDateString())
            ->whereNot('status', '=', 'delivered');
    }

    public function scopeLastOrderAccepted(Builder $query, int $branchId, int $customerId): void
    {
        $query
            ->notArchiveOrder($branchId, $customerId)
            ->where('status', '=', 'accepted');
    }

    public function scopeArchived(Builder $query): void
    {
        $query->whereDate('delivery_date', '<', Carbon::today()->toDateString());
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereDate('delivery_date', '>=', Carbon::today()->toDateString());
    }

    public function scopeLastOrderCanceled(Builder $query, int $branchId, int $customerId): void
    {
        $query
            ->notArchiveOrder($branchId, $customerId)
            ->where('status', '=', 'canceled')
            ->latest('updated_at');
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
            if (Carbon::parse($this['delivery_date'])->lte(Carbon::today()->toDate()) &&
                Carbon::parse($this['delivery_time'])->lt(Carbon::now()->toDateTime()))
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
