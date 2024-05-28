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
        return Country::all()->toArray();

    }

    public function getCities($country)
    {
        return Country::query()->findOrFail($country)->cities->toArray();
    }

}
