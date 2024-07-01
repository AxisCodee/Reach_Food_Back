<?php

namespace App\Actions;

use App\Enums\Roles;
use App\Models\User;

class GetUpperRoleUserIdsAction
{
    public static function handle(User $user, ?int $branchId = null): array
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
                    ->where('city_id', '=', $user->branch->city_id)
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
            $cityId = $user->salesManager()->first()?->branch->city_id;
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
}
