<?php

namespace App\Actions;

use App\Enums\Roles;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;

class GetNotificationUserIdsAction
{
    public static function upperRole(User $user, ?int $branchId = null): array
    {

        $ownerIds = User::query()
            ->where('role', Roles::SUPER_ADMIN->value)
            ->pluck('id')
            ->toArray();

        if ($user->role == Roles::SALES_MANAGER->value) {
            return array_merge(
                $ownerIds,
                User::query()
                    ->where('role', Roles::ADMIN->value)
                    ->where('city_id', '=', $user->branch?->city_id)
                    ->pluck('id')
                    ->toArray()
            );
        }
        if ($user->role == Roles::SALESMAN->value) {
            $ownerIds = array_merge(
                $ownerIds,
                $user
                    ->salesManager()
                    ->when($branchId, function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    })
                    ->pluck('users.id')->toArray()
            );
//            $cityId = $user->salesManager()->first()?->branch()->withTrashed()->first()?->city_id;
            $cityId = $user->salesManager()
                ->join('branches', 'branches.id', '=', 'users.branch_id')
                ->whereNull('branches.deleted_at')
                ->pluck('branches.city_id')
                ->first();
            return array_merge(
                $ownerIds,
                User::query()
                    ->where('role', Roles::ADMIN->value)
                    ->when($cityId, function ($query) use ($cityId) {
                        $query->where('city_id', '=', $cityId);
                    })
                    ->pluck('id')
                    ->toArray()
            );
        }
        return $ownerIds;
    }

    public static function relatedToBranch(int $branchId): array
    {
        $branch = Branch::find($branchId);
        $ownerIds = User::query()
            ->where('role', Roles::CUSTOMER)
            ->whereHas('address', function ($query) use ($branch) {
                $query->where('city_id', $branch?->city_id);
            })
            ->pluck('id')
            ->toArray();
        return array_merge(
            $ownerIds,
            $branch?->salesmen()->pluck('users.id')->toArray()
        );
    }
}
