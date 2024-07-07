<?php

namespace App\Services;

use App\Actions\GetNotificationUserIdsAction;
use App\Enums\NotificationActions;
use App\Enums\Roles;
use App\Models\Contact;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\WorkBranch;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
        $data = $request->only(['name', 'user_name', 'password', 'branch_id', 'address_id', 'location']);
        return DB::transaction(function () use ($data, $request, $user_id) {
            $user = User::query()->findOrFail($user_id);
            $user->update($data);
            if ($request->hasFile('image')) {
                $user->update([
                    'image' => $this->fileService
                        ->update($user->image, $request, 'image'),
                ]);
            }

            if ($user->role != Roles::SUPER_ADMIN->value) {
                if ($request->has('password')) {
                    $user->userPassword->update([
                        'password' => $request['password'],
                    ]);
                }
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
        DB::transaction(function () use ($user, $request) {
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
            $data = [];
            $user->workBranches()->delete();
            if ($branches) {
                foreach ($branches as $branch) {
                    if($branch['salesManager_id']) {
                        $salesManager = User::query()->findOrFail($branch['salesManager_id']);
                        if ($salesManager['role'] != Roles::SALES_MANAGER->value) {
                            throw new Exception('هذا الشخص ليس مدير مبيعات');
                        }
                        logger($branch['branch_id']);
                        if ($salesManager['branch_id'] != $branch['branch_id']) {
                            throw new Exception('هذا المدير لا يتبع لهذا الفرع');
                        }
                    }
                    $work['sales_manager_id'] = $branch['salesManager_id'];
                    $work['salesman_id'] = $user->id;
                    $work['branch_id'] = $branch['branch_id'];
                    $data[] = $work;
                }
                WorkBranch::query()->insert($data);
            }
        });

    }

    public function updateSalesManager($request,User $user)
    {
        DB::transaction(function () use ($request, $user){
            $salesmen = $request['salesmen'];
            $user->managerBranches()->delete();
            if($salesmen)
                foreach ($salesmen as $salesman){
                    $userS = User::query()->findOrFail($salesman);
                    if ($userS['role'] != Roles::SALESMAN->value) {
                        throw new Exception('هذا الشخص ليس مندوب');
                    }
                    WorkBranch::query()->create([
                        'salesman_id' => $salesman,
                        'sales_manager_id'=> $user->id,
                        'branch_id' => $user->branch_id,
                    ]);
                }
        });
    }

    public function show($user)
    {
        return User::query()->with(['contacts', 'address.city.country'])
            ->findOrFail($user);
    }

    public function userAddress($request)
    {
        return User::query()->where('address_id', $request->address_id)
            ->where('role', 'customer')->get()->toArray();
    }

    public function linkTripWithSalesman($trip, $salesmanId)//not used
    {
        return $trip->update([
            'salesman_id' => $salesmanId
        ]);
    }

    public function getUsersByType($request)
    {
        if ($request->role == Roles::CUSTOMER->value) {//By City
            return User::query()
                ->with(['contacts:id,user_id,phone_number', 'address.city.country', 'userPassword:user_id,password'])
                ->where('role', Roles::CUSTOMER->value)
                ->whereHas('address', function ($query) use ($request) {
                    $query->where('city_id', $request->city_id);
                })
                ->when($request->query('no_orders'), function (Builder $query) use ($request) {
                    $query->whereDoesntHave('orders', function ($query) use ($request) {
                        $query->where('branch_id', $request->query('branch_id'));
                    });
                })
                ->get()
                ->toArray();
        }
        if ($request->role == Roles::ADMIN->value) {
            $canShowPassword = auth()->user()->role == Roles::SUPER_ADMIN->value;
            return User::query()
                ->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->when($canShowPassword, function (Builder $query) {
                    $query->with('userPassword:user_id,password');
                })
                ->where('role', Roles::ADMIN->value)
                ->get()->toArray();
        }
        //By Branch
        if ($request->role == Roles::SALES_MANAGER->value) {
            $canShowPassword =
                auth()->user()->role == Roles::SUPER_ADMIN->value ||
                auth()->user()->role == Roles::ADMIN;
            return User::query()
                ->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->when($canShowPassword, function (Builder $query) {
                    $query->with('userPassword:user_id,password');
                })
                ->where('role', Roles::SALES_MANAGER->value)
                ->where('branch_id', $request->branch_id)
                ->get()->toArray();
        } else {//Salesman
            $canShowPassword =
                auth()->user()->role == Roles::SUPER_ADMIN->value ||
                auth()->user()->role == Roles::ADMIN->value ||
                auth()->user()->role == Roles::SALES_MANAGER->value;
            return User::query()
                ->with(['contacts:id,user_id,phone_number', 'address.city.country'])
                ->when($canShowPassword, function (Builder $query) {
                    $query->with('userPassword:user_id,password');
                })
                ->where('role', Roles::SALESMAN->value)
                ->whereHas('workBranches', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                })
                ->get()->toArray();
        }
    }

    public function getSalesmanCustomers($salesman, $request)
    {
        return User::query()
            ->with(['contacts:id,user_id,phone_number', 'address:id,city_id,area'])
            ->whereIn('id', function ($query) use ($salesman, $request) {
                $query->select('customer_times.customer_id')
                    ->from('customer_times')
                    ->join('trips', 'customer_times.trip_id', '=', 'trips.id')
                    ->where('trips.salesman_id', $salesman->id)
                    ->where('trips.branch_id', $request->input('branch_id'));
            })
            ->when($request->has('orders'), function (Builder $query) use ($request) {
                $query->whereHas('orders');
            })
            ->get()
            ->toArray();
    }

    /**
     * @throws Exception
     */
    public function destroy($userId): ?bool
    {
        $user = User::findOrFail($userId);
        if (auth()->user()['role'] == Roles::SALESMAN->value && (($user['added_by'] != auth()->id())))
            throw new Exception("لا يمكنك حذف هذا الزبون");
        $data = [
            'action_type' => NotificationActions::DELETE->value,
            'actionable_id' => $user->id,
            'actionable_type' => User::class,
            'user_id' => auth()->id(),
        ];
        $ownerIds = GetNotificationUserIdsAction::upperRole(auth()->user());

        NotificationService::make($data, 0, $ownerIds);
        return $user->delete();
    }

    public function canAssignCity($cityId): bool
    {
        return ! User::query()
            ->where('role', Roles::ADMIN->value)
            ->where('city_id', $cityId)
            ->exists();
    }

    public function assignCity($user, $cityId): void
    {
        if(! $this->canAssignCity($cityId)){
            throw new Exception('هذا الفرع لديه مدير بالفعل');
        }
        $user->update(['city_id' => $cityId]);
    }

}
