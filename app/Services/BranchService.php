<?php

namespace App\Services;

use App\Models\Branch;

/**
 * Class BranchService.
 */
class BranchService
{


    public function getBranches()
    {
        return Branch::query()
            ->with(['city.country', 'categories:id,name,branch_id'])
            ->with('users', function ($query) {
                $query->where('role', 'admin');
            })
            ->get()
            ->toArray();
    }

    public function showBranch($id)
    {
        return Branch::query()
            ->with(['city.country', 'categories:id,name,branch_id'])
            ->with('users', function ($query) {
                $query->where('role', 'admin');
            })
            ->findOrFail($id);
    }

    public function createBranch($branch)
    {
        return Branch::create([
                'name' => $branch->name,
                'city_id' => $branch->city_id,
            ]
        );
    }

    public function updateBranch($branch)
    {
        return Branch::findOrFail($branch)
            ->update([
                'name' => $branch->name,
                'city_id' => $branch->city_id,
            ]);
    }

    public function deleteBranch($branch)
    {
        return Branch::findOrFail($branch)->delete();
    }

}
