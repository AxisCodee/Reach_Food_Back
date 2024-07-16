<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTime extends Model
{
    protected $guarded = [];
    use HasFactory;

    protected $appends = [
        'fix_arrival_time'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function fixArrivalTime(): Attribute
    {
        return Attribute::get(function (){
            return Carbon::make($this['arrival_time'])->format('H:i');
        });
    }
}
