<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

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
        'branch_id',
        'address_id',
        'location',
        'image',
        'city_id'
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
                return [$permission->name => (bool)$status];
            });
        } else return null;
    }


    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'salesman_id');
    }

    public function todayTripsDates(): HasManyThrough
    {
        return $this
            ->hasManyThrough(TripDates::class, Trip::class, 'salesman_id')
            ->whereDate('start_date', '=', Carbon::today());

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

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function salesManager(): BelongsToMany//
    {
        return $this->belongsToMany(User::class, 'work_branches', 'salesman_id', 'sales_manager_id');
    }

    public function salesman(): belongsToMany//
    {
        return $this
            ->belongsToMany(User::class, 'work_branches', 'sales_manager_id', 'salesman_id');

    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'user_notifications', 'owner_id', 'notification_id')
            ->with('user')
            ->withPivot('read');
    }

    public function userPassword(): HasOne
    {
        return $this->hasOne(UsersPassword::class, 'user_id');
    }

    public function workBranches(): HasMany
    {
        return $this->hasMany(WorkBranch::class, 'salesman_id');
    }

    public function managerBranches(): HasMany
    {
        return $this->hasMany(WorkBranch::class, 'sales_manager_id');
    }

}
