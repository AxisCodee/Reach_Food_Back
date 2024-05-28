<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Contact;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService.
 */
class UserService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function createUserDetails(Request $request, $user_id)
    {
        UserDetail::query()->create([
            'user_id' => $user_id,
            'image' => $this->fileService->upload($request, 'image'),
            'address_id' => $request->address_id,
            'location' => $request->location,
        ]);
        foreach ($request['phone_number'] as $item)
            Contact::query()->create([
                'user_id' => $user_id,
                'phone_number' => $item
            ]);
        return true;
    }

    public function updateUserDetails(Request $request, $user_id)
    {
        return DB::transaction(function () use ($request, $user_id) {
            $user = User::query()->findOrFail($user_id);
            $user->update([
                'name' => $request->name,
                'user_name' => $request->user_name,
                'password' => Hash::make($request['password']),
                'branch_id' => $request->branch_id,
            ]);
            $userDetail = $user->userDetails;
            $userDetail->update([
                'image' => $this->fileService
                    ->update($userDetail->image, $request, 'image'),
                'address_id' => $request['address_id'],
                'location' => $request['location'],
            ]);
            $contacts = $request['phone_number'];
            if ($contacts) {
                $user->contacts()->delete();
                foreach ($contacts as $item) {
                    Contact::query()->create([
                        'user_id' => $user_id,
                        'phone_number' => $item
                    ]);
                }
            }
            return true;
        });
    }

    public function Show()
    {
        $result = User::get();
        return $result;
    }

    public function linkWithSalesManager($salesman, $salesManager_id)
    {
        return User::query()->findOrFail($salesman)
            ->update([
                'salesManager_id' => $salesManager_id
            ]);
    }

    public function linkTripWithSalesman($trip, $salesmanId)
    {
        return $trip->update([
            'salesman_id' => $salesmanId
        ]);
    }

//    public function getBranchCustomers($request)
//    {
//        return User::query()->with(['contacts:id,user_id,phone_number', 'userDetails.address.city.country'])
//            ->where('role', 'customer')
//            ->where('branch_id', $request->branch_id)
//            ->get()->toArray();
//    }

//    public function getCategoryUsers($request)
//    {
//        return User::query()->with(['contacts:id,user_id,phone_number', 'branch.city.country'])
//            ->where('role', $request->role)
//            ->whereHas('categories', function ($query) use ($request) {
//                $query->where('category_id', $request->category_id);
//            })
//            ->get()->toArray();
//    }

//    public function getAdmins()
//    {
//        return User::query()->with(['contacts:id,user_id,phone_number', 'userDetails.address.city.country',
//            'branch.city.country'])
//            ->where('role', 'admin')
//            ->get()->toArray();
//    }


    public function getUsersByType($request)
    {
        if ($request->role == Roles::CUSTOMER->value) {
            return User::query()->with(['contacts:id,user_id,phone_number', 'userDetails.address.city.country'])
                ->where('role', 'customer')
                ->where('branch_id', $request->branch_id)
                ->get()->toArray();
        }
        if ($request->role == Roles::ADMIN->value) {
            return User::query()->with(['contacts:id,user_id,phone_number', 'userDetails.address.city.country',
                'branch.city.country'])
                ->where('role', 'admin')
                ->get()->toArray();
        } else {
            return User::query()->with(['contacts:id,user_id,phone_number', 'branch.city.country'])
                ->where('role', $request->role)
                ->whereHas('categories', function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                })
                ->get()->toArray();
        }
    }

}
