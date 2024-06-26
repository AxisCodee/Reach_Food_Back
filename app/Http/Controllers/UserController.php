<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $users = $this->userService->getUsersByType($request);
        return ResponseHelper::success($users);
    }

    public function show($id)//???
    {
        $result = $this->userService->show($id);
        return ResponseHelper::success($result);
    }

    public function showSalesMan($id)
    {
        $day = \request()->input('day');
        $branchId = \request()->input('branch_id');
        $result = User::query()
            ->with([
                'contacts',
                'address.city.country',
                'workBranches' => function ($query) {
                    $query->with(['branch', 'salesManager']);
                },
                'trips' => function ($query) use ($day, $branchId) {
                    $query
                        ->when($day, function ($query) use ($day) {
                            $query->where('day', $day);
                        })
                        ->where('branch_id', $branchId)
                        ->with('customers');
                }
            ])
            ->where('role', Roles::SALESMAN)
            ->findOrFail($id);
        return ResponseHelper::success($result);
    }

    public function userAddress(Request $request)
    {
        $result = $this->userService->userAddress($request);
        return ResponseHelper::success($result);
    }

    public function destroy($user)
    {
        $result = $this->userService->destroy($user);

        if ($result) {
            return ResponseHelper::success('User deleted successfully.');
        }
        return ResponseHelper::error('المستخدم غير موجود');
    }

    public function restore($id)
    {
        $u = User::onlyTrashed()->findOrFail($id);
        if($u){
            $u->restore();
            return ResponseHelper::success('User restored successfully.');
        }
        return ResponseHelper::error('المستخدم غير موجود');
    }


    public function update(UpdateUserRequest $request, $user_id)
    {
        $result = $this->userService->updateUser($request, $user_id);
        $user = User::findOrFail($user_id);
        if ($user->role == Roles::SALESMAN->value) {
            $this->userService->updateSalesman($request, $user);
        }
        if ($user->role == Roles::SALES_MANAGER->value) {
            $this->userService->updateSalesManager($request, $user);
        }
        if ($result) {
            return ResponseHelper::success('User updated successfully.');
        }
        return ResponseHelper::error('حدث خطأ في تحديث بيانات المستخدم');
    }

    public function getSalesmanCustomers()
    {
        $salesman = User::findOrFail(auth('sanctum')->id());//auth
        $customers = $this->userService->getSalesmanCustomers($salesman);
        return ResponseHelper::success($customers);
    }

}
