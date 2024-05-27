<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'password',
        'role',
        'customer_type',
        'userDetails_id',
        'branch_id',
        'salesManager_id',
        'superAdmin_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = ['permissions'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getPermissionsAttribute()
    {
        $userPermissions = UserPermission::where('user_id', $this->id)
            ->get()
            ->keyBy('permission_id');

        $permissions = Permission::get();
        if ($permissions || $userPermissions) {
            return $permissions->mapWithKeys(function ($permission) use ($userPermissions) {
                $userPermission = $userPermissions->get($permission->id);
                $status = $userPermission ? $userPermission->status : null;
                return [$permission->name => $status];
            });
        } else return null;
    }

    public function userDetails(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'salesman_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_category');
    }


    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function salesmen(): HasMany
    {
        return $this->hasMany(User::class, 'salesManager_id');

    }

    public function customers(): HasMany
    {
        return $this->hasMany(User::class, 'salesman_id');
    }


}
