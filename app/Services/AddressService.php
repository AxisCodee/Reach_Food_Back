<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AddressService.
 */
class AddressService
{
    public function getAddresses()
    {

    }

    public function getCountries()
{
    if(auth()->user()['role'] == Roles::SUPER_ADMIN->value)
        return Country::query()
            ->with(['cities' => function ($query) {
                $query->whereHas('branch');
            }])
            ->get()
            ->map(function ($country) {
                $country->cities = $country->cities->filter(function ($city) {
                    return $city->branch;
                });
                return $country;
            })
            ->toArray();
    elseif (auth()->user()['role'] == Roles::ADMIN->value) {
        $cityId = auth()->user()['city_id'];
        return Country::query()
            ->whereHas('cities', function ($query) use ($cityId) {
                $query->where('id', $cityId);
            })
            ->with(['cities' => function ($query) use ($cityId) {
                $query->where('id', $cityId)->whereHas('branch');
            }])
            ->get()
            ->map(function ($country) {
                $country->cities = $country->cities->filter(function ($city) {
                    return $city->branch;
                });
                return $country;
            })
            ->toArray();
    }else{
        return [];
    }

}
    public function getCities($country)
    {
        return Country::query()->findOrFail($country)->cities->toArray();
    }

}
