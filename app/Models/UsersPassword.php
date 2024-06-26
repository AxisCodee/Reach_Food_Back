<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersPassword extends Model
{
    protected $fillable = [
        'user_id',
        'password',
    ];
    protected $table = 'users_passwords';
    protected $primaryKey = 'user_id';

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decrypt($value),
            set: fn($value) => encrypt($value)
        );
    }
}
