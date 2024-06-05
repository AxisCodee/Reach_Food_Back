<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Address;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddressController extends Controller
{

    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function importFromJson(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $request->validate([
                'json' => 'required|file|mimes:json',
            ]);
            $jsonFile = $request->file('json');
            $data = json_decode(file_get_contents($jsonFile->getRealPath()), true);
            foreach ($data as $item) {
                Address::create([
                    'city_id' => 5,
                    'area' => $item['AREA_DESCRIPTION'], // Access the property correctly
                ]);
            }
            return ResponseHelper::success('Address imported successfully!');
        });
    }

    public function getAddresses($city)
    {
        $city = City::findOrFail($city);
        $addresses = $city->addresses->toArray();
        return ResponseHelper::success($addresses);
    }

    public function getCountries()
    {
        $countries = $this->addressService->getCountries();
        return ResponseHelper::success($countries);
    }

    public function getCities($country)
    {
        $cities = $this->addressService->getCities($country);
        return ResponseHelper::success($cities);
    }

    public function allCities()
    {
        $cities = City::with('country')->get()->toArray();
        return ResponseHelper::success($cities);
    }


    public function deleteBranches(Request $request)
    {
        $user_name = auth('sanctum')->user()->user_name;
        $user = User::where('user_name', $user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        $cities = $request['cities'];
        City::whereIn('id', $cities)->with('branch')->get()->each(function ($city) {
            $city->branch->each(function ($branch) {
                $branch->delete();
            });
        });
        return ResponseHelper::success('deleted');
    }

}
