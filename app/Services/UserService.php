<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                ''
            ]);
            $userDetail = $user->userDetails;
            $userDetail->update([
                'image' => $this->fileService
                    ->update($userDetail->image, $request->file('image'), 'image'),
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

    public function getUsersByType($request)
    {
        $result = User::query()->with(['contacts:id,user_id,phone_number', 'userDetails.address.city.country'])
            ->where('role', $request->role)
            ->where('branch_id', $request->branch_id)
            ->get()->toArray();
        return $result;
    }

}
