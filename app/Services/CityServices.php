<?php

namespace App\Services;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
use App\Enums\Roles;
use App\Models\Branch;
use App\Models\User;

class CityServices
{


    public function getNameOfBranch($cityId, $name){
        $originalName = $name;
        $counter = 1;
        while (
            Branch::
                where('city_id', $cityId)
                ->where('name', $name)
                ->exists()
        ) {
            $name = $originalName . '-' . $counter;
            $counter++;
        }

        return $name;
    }
    public function insertBranchesInto($cityId, $branches): void
    {
        $insertData = [];
        foreach ($branches as $branch) {
            $insertData[] = [
                'city_id' => $cityId,
                'name' => $this->getNameOfBranch($cityId, $branch),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Branch::query()->insert($insertData);
    }

    public function setAdmin($cityId, $adminId): void
    {
        $admin = User::query()
            ->where('role', Roles::ADMIN->name)
            ->whereNull('city_id')
            ->findOrFail($adminId);

        $admin->update(['city_id' => $cityId]);
    }

    public function updateBranches($cityId, $branches): void
    {
        $idsForNotDelete = [];
        $insertData = [];
        foreach ($branches as $branch) {
            if($branch['id']){
                Branch::query()
                    ->find($branch['id'])
                    ->update([
                        'name' => $this->getNameOfBranch($cityId,  $branch['name']),
                        'updated_at' => now(),
                    ]);
                $idsForNotDelete[] = $branch['id'];
            }else{
                $insertData[] = $branch['name'];
            }
        }
        $deletedBranches = Branch::query()
            ->where('city_id', $cityId)
            ->whereNotIn('id', $idsForNotDelete)
            ->get();
        $this->deleteBranches($deletedBranches);
        $this->insertBranchesInto($cityId, $insertData);
    }


    private function deleteBranches($branches): void
    {
        foreach ($branches as $branch) {
            $data = [
                'action_type' => NotificationActions::DELETE->value,
                'actionable_id' => $branch->id,
                'actionable_type' => Branch::class,
                'user_id' => auth()->id(),
            ];
            $ownerIds = GetUpperRoleUserIdsAction::handle(auth()->user());
            $ownerIds[] = auth()->id();
            NotificationService::make($data, 0, $ownerIds);
            $branch->delete();
        }
    }
    private function deleteOldAdmin($cityId): void
    {
        User::query()
            ->where('city_id', $cityId)
            ->where('role', Roles::ADMIN->value)
            ->first()
            ?->update(['city_id' => null]);
    }

    public function updateAdmin($cityId, $adminId): void
    {
        $this->deleteOldAdmin($cityId);
        if($adminId){
            $this->setAdmin($cityId, $adminId);
        }
    }
}
