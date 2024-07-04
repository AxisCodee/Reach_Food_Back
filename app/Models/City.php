<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class City extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function branch(): HasMany
    {
        return $this->hasMany(Branch::class,'city_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class,'city_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class,'city_id')
           ;
    }
}
