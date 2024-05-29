<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDates extends Model
{
    protected $guarded = [];

    use HasFactory;


    public function trip()
    {
        return $this->belongsTo(TripDates::class);
    }
}
