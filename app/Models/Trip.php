<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['day_ar', 'fix_start_time', 'fix_end_time'];

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

//    public function orders(): HasMany
//    {
//        return $this->hasMany(Order::class);
//    }
    // public function date(): HasMany
    // {
    //     return $this->hasMany(TripDates::class);
    // }

    public function dates(): HasMany
    {
        return $this->hasMany(TripDates::class);
    }


    public function customerTimes(): HasMany{
        return $this->hasMany(CustomerTime::class, 'trip_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_times', 'trip_id', 'customer_id');
    }

    public function dayAr(): Attribute
    {
        return Attribute::get(function () {
            $translateDays = [
                'Sunday' => 'الأحد',
                'Monday' => 'الإثنين',
                'Tuesday' => 'الثلاثاء',
                'Wednesday' => 'الأربعاء',
                'Thursday' => 'الخميس',
                'Friday' => 'الجمعة',
                'Saturday' => 'السبت',
            ];

            return $translateDays[$this['day']];
        });
    }

    public function fixStartTime(): Attribute
    {
        return Attribute::get(function () {
            return Carbon::make($this['start_time'])->format('H:i');
        });
    }

    public function fixEndTime(): Attribute
    {
        return Attribute::get(function () {
            return Carbon::make($this['end_time'])->format('H:i');
        });
    }

}
