<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded=[];
    // protected $fillable = [
    //     'name',
    //     'category',
    //     'description',
    //     'stock_quantity',
    //     'weight',
    //     'weight_unit',
    //     'wholesale_price',
    //     'retail_price',
    //     'image',
    //     'color',
    //     'size'
    // ];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
}
