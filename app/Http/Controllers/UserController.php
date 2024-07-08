<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\GetSalesmanCustomersRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

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
        try {
            $this->userService->destroy($user);
            return ResponseHelper::success(null, null, 'User deleted successfully.');
        } catch (ModelNotFoundException) {
            return ResponseHelper::error(null, null, 'المستخدم غير موجود', 404);
        } catch (Exception $e) {
            return ResponseHelper::error(null, null, $e->getMessage());
        }
    }

    public function restore($id)
    {
        $u = User::onlyTrashed()->findOrFail($id);
        if ($u) {
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
        if ($user->role == Roles::ADMIN->value){
            if($request->city_id && $user['city_id'] != $request->city_id)
                $this->userService->assignCity($user, $request->city_id);
            $user->load(['city', 'contacts']);
        }
        if ($result) {
            return ResponseHelper::success($user);
        }
        return ResponseHelper::error('حدث خطأ في تحديث بيانات المستخدم');
    }

    public function getSalesmanCustomers(GetSalesmanCustomersRequest $request)
    {
        $salesman = auth()->user();
        $customers = $this->userService->getSalesmanCustomers($salesman, $request);
        return ResponseHelper::success($customers);
    }

    public function adminsWithoutCity()
    {
        return ResponseHelper::success(
            User::query()
                ->select('id', 'name')
                ->where('role', Roles::ADMIN->value)
                ->whereNull('city_id')
                ->get()
                ->toArray()
        );
    }

}
