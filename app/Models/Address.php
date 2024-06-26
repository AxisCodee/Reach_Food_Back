<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    } public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }



}
