<?php

namespace App\Http\Controllers;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateBranchRequest;
use App\Http\Requests\DeleteBranchesRrequest;
use App\Http\Requests\DeleteBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use App\Services\BranchService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            $branches = $request['branches'];
            foreach ($branches as $branch) {
                $this->branchService->createBranch($branch, $request->city_id);
            }
            if ($request->salesManager_id) {
                $salesManager = User::findOrFail($request->salesManager_id);
                $salesManager->update(['city_id' => $request->city_id]);
            }
            return ResponseHelper::success('Branched added successfully');
        });
    }

    public function show($branch)
    {
        $result = $this->branchService->showBranch($branch);
        return ResponseHelper::success($result);
    }

    public function update($id, UpdateBranchRequest $request)
    {
        $branch = Branch::findOrFail($id);
        $result = $this->branchService->updateBranch($branch, $request);
        return ResponseHelper::success('Branch updated successfully');

    }

    public function delete(DeleteBranchesRrequest $request)
    {
        $user_name = auth('sanctum')->user()->user_name;
        $user = User::where('user_name', $user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        $result = $this->branchService->deleteBranches($request);
        if ($result) {
            return ResponseHelper::success('deleted');
        }
        return ResponseHelper::error('Something went wrong.', 500);
    }

    public function deleteBranch(DeleteBranchRequest $request, $id)
    {
        $user_name = auth('sanctum')->user()->user_name;
        $user = User::where('user_name', $user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        $branch = Branch::query()->findOrFail($id);
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

        return ResponseHelper::success('deleted');
    }

    public function restore($id)
    {
        $b = Branch::onlyTrashed()->firstWhere('id', $id);
        if ($b) {
            $b->restore();
            return ResponseHelper::success('restored');
        }
        return ResponseHelper::error('Not Found.', 404);
    }

    public function branches()
    {
        $result = City::with([
            'country',
            'branch.users' => function ($query) {
                $query->where('role', 'admin');
            }
        ])
            ->whereHas('branch.users', function ($query) {
                $query->where('role', 'admin');
            })
            ->get()
            ->toArray();
        return ResponseHelper::success($result);
    }

    public function salesmanBranches()
    {
        return ResponseHelper::success(
            $this->branchService->getBranchesForSalesman()
        );
    }

}
