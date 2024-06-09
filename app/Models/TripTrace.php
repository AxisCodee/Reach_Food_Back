<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripTrace extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tripDate()
    {
        return $this->belongsTo(TripDates::class);
    }

}
