<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

class Trip extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    // public function date(): HasMany
    // {
    //     return $this->hasMany(TripDates::class);
    // }

    public function dates(): HasMany
    {
        return $this->hasMany(TripDates::class);
    }

}
