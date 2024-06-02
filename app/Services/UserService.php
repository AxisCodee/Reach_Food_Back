<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Contact;
use App\Models\User;
use App\Models\UserPermission;
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

    public function createUserContacts($contacts, $user_id)
    {
        foreach ($contacts as $contact)
            Contact::query()->create([
                'user_id' => $user_id,
                'phone_number' => $contact
            ]);
        return true;
    }

    public function updateUser(Request $request, $user_id)
    {
        return DB::transaction(function () use ($request, $user_id) {
            $user = User::query()->findOrFail($user_id);
            $user->update([
                'name' => $request->name,
                'user_name' => $request->user_name,
                'password' => Hash::make($request['password']),
                'branch_id' => $request->branch_id,
                'address_id' => $request->address_id,
                'location' => $request->location,
            ]);
            if ($request->hasFile('image')) {
                $user->update([
                    'image' => $this->fileService
                        ->update($user->image, $request, 'image'),
                ]);
            }
            $contacts = $request['phone_number'];
            if ($contacts) {
                $user->contacts()->delete();
                $this->createUserContacts($contacts, $user_id);
            }
            return true;
        });
    }

    public function updateSalesman($request, $user)
    {
        //update permissions
        $permissions = $request['permissions'];
        if ($permissions) {
            foreach ($permissions as $index => $permission) {
                $status = $permission['status'];
                UserPermission::query()->where('user_id', $user->id)
                    ->where('permission_id', $index + 1)
                    ->update([
                        'status' => $status
                    ]);
            }
        }
        //update branches
        // Get branches data from the request
        $branches = $request['branches'];
        if ($branches) {
            $user->salesManager()->detach();
            foreach ($branches as $branch) {
                $user->salesManager()->attach($branch['salesManager_id']);
            }
        }

    }

    public function updateSalesManager($request, $user)
    {
        $user->update([//link with category(branch)
            'branch_id' => $request->branch_id
        ]);
        //link with salesmen
        $salesmen = $request['salesmen'];
        if ($salesmen) {
            $user->salesman()->detach();
            $user->salesman()->attach($salesmen);
        }

    }

    public function show($user)
    {
        return User::query()->with(['contacts', 'address.city.country'])
            ->findOrFail($user);
    }

    public function linkTripWithSalesman($trip, $salesmanId)
    {
        return $trip->update([
            'salesman_id' => $salesmanId
        ]);
    }


    public function getUsersByType($request)
    {
        if ($request->role == Roles::CUSTOMER->value) {//By City
            return User::query()->with(['contacts:id,user_id,phone_number', 'address.city'])
                ->where('role', Roles::CUSTOMER->value)
                ->whereHas('address', function ($query) use ($request) {
                    $query->where('city_id', $request->city_id);//TODO city Customers
                })
                ->get()->toArray();
        }
        if ($request->role == Roles::ADMIN->value) {
            return User::query()->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->where('role', Roles::ADMIN->value)
                ->get()->toArray();
        }
        //By Branch
        if ($request->role == Roles::SALES_MANAGER->value) {
            return User::query()->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->where('role', Roles::SALES_MANAGER->value)
                ->where('branch_id', $request->branch_id)
                ->get()->toArray();
        } else {//Salesman
            return User::query()->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->where('role', Roles::SALESMAN->value)
                ->whereHas('salesManager', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                })
                ->get()->toArray();
        }

    }

}
