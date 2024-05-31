<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index()
    {
        $result = $this->branchService->getBranches();
        return ResponseHelper::success($result);
    }

    public function store(CreateBranchRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $branch = $this->branchService->createBranch($request);
            if ($request->salesManager_id) {
                $salesManager = User::findOrFail($request->salesManager_id);
                $salesManager->update(['branch_id' => $branch->id]);
            }
            return ResponseHelper::success($branch);
        });
    }

    public function show($branch)
    {
        $result = $this->branchService->showBranch($branch);
        return ResponseHelper::success($result);
    }

    public function update($id, UpdateBranchRequest $request)
    {
        return DB::transaction(function () use ($id, $request) {
            $branch = Branch::findOrFail($id);
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
            return ResponseHelper::success($branch);
        });
    }

    public function destroy($branch)
    {
        User::query()->where('branch_id', $branch)->update([
            'branch_id' => null
        ]);
        $result = $this->branchService->deleteBranch($branch);
        if ($result) {
            return ResponseHelper::success($result);
        }
        return ResponseHelper::error('Branch not deleted.');
    }

    public function deleteBranches(Request $request)//
    {
        $branches = $request['branches'];
        foreach ($branches as $branch) {
            $this->branchService->deleteBranch($branch);
        }
        return ResponseHelper::success('Branches deleted successfully.');
    }


}
