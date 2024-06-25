<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UpdateUserRequest;
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

    public function index(Request $request)
    {
        $users = $this->userService->getUsersByType($request);
        return ResponseHelper::success($users);
    }

    public function show($user)//???
    {
        $result = $this->userService->show($user);
        return ResponseHelper::success($result);
    }

    public function userAddress(Request $request)
    {
        $result = $this->userService->userAddress($request);
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
        return ResponseHelper::error('User not updated.');
    }

    public function getSalesmanCustomers()
    {
        $salesman = User::findOrFail(auth('sanctum')->id());//auth
        $customers = $this->userService->getSalesmanCustomers($salesman);
        return ResponseHelper::success($customers);
    }

}
