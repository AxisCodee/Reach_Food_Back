<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $appends = [
        'name_ar'
    ];

    public function nameAr(): Attribute
    {
        return  Attribute::get(function (){
            $translate = [
                'add' => 'إضافة',
                'edit' => 'حذف',
                'delete' => 'تعديل',
            ];
            return $translate[$this['name']];
        });
    }
}
