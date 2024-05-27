<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Address;
use App\Models\Branch;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function branchAddresses($branch)//return addresses form branch city
    {
        $city = Branch::findOrFail($branch)->city()->first();
        $addresses = $city->addresses;
        return ResponseHelper::success($addresses->toArray());
    }

    public function getCities($country)
    {
        $cities = $this->addressService->getCities($country);
        return ResponseHelper::success($cities);
    }

}
