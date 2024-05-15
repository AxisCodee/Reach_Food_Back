<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'city_id',
        'area'
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function usersDetails(): HasMany
    {
        return $this->hasMany(UserDetail::class);
    }

}
