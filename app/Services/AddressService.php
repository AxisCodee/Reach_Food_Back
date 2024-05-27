<?php

namespace App\Services;

use App\Models\City;
use App\Models\Country;

/**
 * Class AddressService.
 */
class AddressService
{
    public function getAddresses()
    {

    }

    public function getCities($country)
    {
        return Country::query()->findOrFail($country)->cities->toArray();
    }

}
