<?php

namespace App\Services;

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
}
    public function getCities($country)
    {
        return Country::query()->findOrFail($country)->cities->toArray();
    }

}
