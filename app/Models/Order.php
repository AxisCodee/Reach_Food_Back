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

    public function scopeThisWeek(Builder $query): Builder
    {
        $startOfWeek = Carbon::now()->startOfWeek(CarbonInterface::SATURDAY);
        $endOfWeek = Carbon::now()->endOfWeek(CarbonInterface::FRIDAY);
        return $query->whereBetween('order_date', [$startOfWeek, $endOfWeek]);
    }

    protected function canUndo(): Attribute
    {
        return Attribute::get(function () {
            if (Carbon::parse($this['order_date'])->lt(Carbon::today()->toDate()))
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

}
