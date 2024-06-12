<?php

namespace App\Services;

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
        return Branch::query()
            ->with(['city.country'])
            ->with('users', function ($query) {
                $query->where('role', 'sales manager');
            })
            ->get()
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
                $branch->delete();
            });
        });
        return true;
    }

}
