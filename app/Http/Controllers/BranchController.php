<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Category;
use App\Models\User;
use App\Services\BranchService;
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
            $categories = $request['categories'];
            if ($categories) {
                foreach ($categories as $item) {
                    $category = Category::create(['name' => $item['name']]);
                    $category->update(['branch_id' => $branch->id]);
                }
            }

            if ($request->admin_id){
                $admin = User::findOrFail($request->admin_id);
                $admin->update(['branch_id' => $branch->id]);
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
            $branch->update(['city_id' => $request['city_id']]);
            $categories = $request['categories'];
            if ($categories) {
                foreach ($categories as $item) {
                    Category::firstOrCreate(
                        ['name' => $item['name'], 'branch_id' => $branch->id],
                    );
                }
            }
            $oldAdmin = $branch->users()
                ->where('role', 'admin')
                ->where('branch_id', $branch->id)->first();
            $oldAdmin->update(['branch_id' => null]);
            $admin = User::findOrFail($request->admin_id);
            $admin->update(['branch_id' => $branch->id]);
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


}
