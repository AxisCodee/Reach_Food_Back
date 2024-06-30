<?php

namespace App\Services;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
use App\Enums\Roles;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UsersPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    private $user;

    public function createUser(Request $request)
    {
        $baseData = $request->only([
            'name',
            'user_name',
            'role',
            'address_id',
            'location',
        ]);
        $baseData['image'] = app(FileService::class)->upload($request, 'image');
        $baseData['password'] = Hash::make($request['password']);


        $role = Roles::from($request->role);
        //Adding Data
        switch ($role) {
            case Roles::ADMIN :
                $baseData['city_id'] = $request->city_id;
                break;
            case Roles::CUSTOMER :
                $baseData['customer_type'] = $request->customer_type;
                break;
            case Roles::SALES_MANAGER :
                $baseData['branch_id'] = $request->branch_id;
                break;
            case Roles::SALESMAN :
                $baseData['branch_id'] = $request->input('branch_id');
        }
        $this->user = User::create($baseData);
        $data = [
            'action_type' => NotificationActions::ADD->value,
            'actionable_id' => $this->user->id,
            'actionable_type' => User::class,
            'user_id' => auth()->id(),
        ];
        $ownerIds = GetUpperRoleUserIdsAction::handle(auth()->user());
        NotificationService::make($data, 0, $ownerIds);


        //Attaching
        switch ($role) {
            case Roles::SALES_MANAGER :
                $this->attachForSalesManager($request);
                break;
            case Roles::SALESMAN:
                $this->attachForSalesMan($request);
        }

        if ($role != Roles::SUPER_ADMIN) {
            UsersPassword::query()->create([
                'user_id' => $this->user->id,
                'password' => $request['password']
            ]);
        }

        return $this->user;
    }

    private function attachForSalesManager($request): void
    {
        //link with salesmen
        $salesmen = $request['salesmen'];
        if ($salesmen) {
            // $user->salesManager()->attach($salesmen);
            $this->user->salesman()->attach($salesmen);
        }
    }

    private function attachForSalesMan($request): void
    {
        //assign permissions
        $permissions = $request['permissions'];
        if ($permissions) {
            $data = [];
            foreach ($permissions as $index => $permission) {
                $status = $permission['status'];
                $data[] = [
                    'permission_id' => $index + 1,
                    'user_id' => $this->user->id,
                    'status' => $status
                ];
            }
            UserPermission::query()->insert($data);
        }
        //TRIPS
        $trips = $request['trips'];
        if ($trips) {
            for ($i = 0; $i < count($trips); $i++) {
                for ($j = 0; $j < count($trips); $j++) {
                    $time1 = Carbon::make($trips[$i]['start_time'])->toDateTime();
                    $time2 = Carbon::make($trips[$i]['end_time'])->toDateTime();
                    $time3 = Carbon::make($trips[$j]['start_time'])->toDateTime();
                    $time4 = Carbon::make($trips[$j]['end_time'])->toDateTime();
                    if ($time1 > $time3
                        && $time1 < $time4) {
                        throw new \Exception('الاوقات متضاربة');
                    }
                    if ($time2 > $time3
                        && $time2 < $time4) {
                        throw new \Exception('الاوقات متضاربة');
                    }
                }
            }
            foreach ($trips as $trip) {
                $trip['branch_id'] = $request->input('branch_id');
                $trip['salesman_id'] = $this->user->id;
                app(TripService::class)->createTrip($trip);
            }
        }
        // link with categories
        $branches = $request['branches'];
        $attached = [];
        if ($branches) {
            foreach ($branches as $branch) {
                $attached = $branch['salesManager_id'];
            }
            $this->user->salesManager()->attach($attached);
        }
    }
}
