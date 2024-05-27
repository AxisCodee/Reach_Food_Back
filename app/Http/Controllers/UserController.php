<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
//    public function show()//???
//    {
//        $result =app(UserService::class)->show();
//        return ResponseHelper::success($result, null, 'products returned successfully', 200);
//    }

    public function index(Request $request)
    {
        $users = $this->userService->getUsersByType($request);
        return ResponseHelper::success($users);
    }

    public function getPermissions()
    {
        $result = Permission::query()->get()->toArray();
        return ResponseHelper::success($result);
    }

    public function destroy($user)
    {
        $result = User::findOrFail($user)->delete();
        if ($result) {
            return ResponseHelper::success('User deleted successfully.');
        }
        return ResponseHelper::error('User not found.');
    }

    public function update(UpdateUserRequest $request, $user)
    {
        $result = $this->userService->updateUserDetails($request, $user);
        if ($result) {
            return ResponseHelper::success('User updated successfully.');
        }
        return ResponseHelper::error('User not updated.');
    }

    public function getSalesmanCustomers(Request $request)
    {
        $salesman = User::query()->findOrFail(10);//auth
        $customers = $salesman->customers;
        return ResponseHelper::success($customers);

    }

}
