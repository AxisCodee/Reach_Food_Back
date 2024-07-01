<?php

namespace App\Services;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Class BranchService.
 */
class BranchService
{


    public function getBranches()
    {
        $cityId = request('city_id');
        return Branch::query()
            ->with(['city.country'])
            ->with('users', function ($query) {
                $query->where('role', 'sales manager');
            })
            ->when($cityId, function ($query) use ($cityId) {
                return $query->where('city_id', $cityId);
            })
            ->get()
            ->toArray();
    }

    public function getBranchesForSalesman()
    {
        return auth()
            ->user()
            ->workBranches()
            ->whereHas('branch')
            ->with('branch:id,name')
            ->get()
            ->pluck('branch')
            ->toArray();
    }

    public function showBranch($id)
    {
        return Branch::query()
            ->with(['city.country'])
            ->with('users', function ($query) {
                $query->where('role', 'sales manager');
            })
            ->findOrFail($id);
    }

    public function createBranch($branch, $city_id)
    {
        return Branch::create([
                'name' => $branch,
                'city_id' => $city_id,
            ]
        );
    }

//    public function updateBranch($branch)
//    {
//        return Branch::findOrFail($branch)
//            ->update([
//                'name' => $branch->name,
//                'city_id' => $branch->city_id,
//            ]);
//    }

    public function updateBranch($branch, $request)
    {
        return DB::transaction(function () use ($branch, $request) {
            $branch->update([
                'name' => $request['name'],
                'city_id' => $request['city_id']
            ]);
            $oldSalesManager = $branch->users()
                ->where('role', 'sales manager')
                ->where('branch_id', $branch->id)->first();
            $oldSalesManager->update(['branch_id' => null]);
            $newSalesManager = User::findOrFail($request->salesManager_id);
            $newSalesManager->update(['branch_id' => $branch->id]);
        });
    }


    public function deleteBranches($request)
    {
        $cities = $request['cities'];
        City::whereIn('id', $cities)->with('branch')->get()->each(function ($city) {
            $city->branch->each(function ($branch) {
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
            });
        });
        return true;
    }

}
