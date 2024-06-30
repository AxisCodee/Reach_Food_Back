<?php

namespace App\Actions;

use App\Enums\Roles;
use App\Models\User;

class GetUpperRoleUserIdsAction
{
    public static function handle(User $user): array
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
            $ownerIds = array_merge($ownerIds, $user->salesManager()->pluck('users.id')->toArray());
            return array_merge(
                $ownerIds,
                User::query()
                    ->where('role', Roles::ADMIN->value)
                    ->where('city_id', '=', $user->salesManager()->first()->branch->city_id)
                    ->pluck('id')
                    ->toArray()
            );
        }
        return $ownerIds;
    }
}
