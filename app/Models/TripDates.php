<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TripDates extends Model
{
    protected $guarded = [];

    use HasFactory;


    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'trip_date_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function tripTrace()
    {
        return $this->hasOne(TripTrace::class, 'trip_date_id');
    }

    public function notification(): MorphOne
    {
        return $this->morphOne(Notification::class, 'notificationable');
    }
}
