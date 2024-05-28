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

    public function getSalesmanCustomers()
    {
        $salesman = User::query()->findOrFail(4);//auth
        $customers = $salesman->with(['trips:.orders.customer'])->get()->toArray();
        return ResponseHelper::success($customers);

    }

//    public function branchCustomers(Request $request)
//    {
//        $customers = $this->userService->getBranchCustomers($request);
//        return ResponseHelper::success($customers);
//
//    }

//    public function categoryUsers(Request $request)
//    {
//        $customers = $this->userService->getCategoryUsers($request);
//        return ResponseHelper::success($customers);
//
//    }
//
//    public function admins()
//    {
//        $admins = $this->userService->getAdmins();
//        return ResponseHelper::success($admins);
//
//    }

}
