<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function importFromJson(Request $request)
    {
//        Address::query()->where('city_id',5)->delete();
//        return;
        return DB::transaction(function () use ($request) {
            $request->validate([
                'json' => 'required|file|mimes:json',
            ]);

// Get the file from the request
            $jsonFile = $request->file('json');

// Convert the file contents to an array
            $data = json_decode(file_get_contents($jsonFile->getRealPath()), true);

// Loop through the data and create new database records
            foreach ($data as $item) {
                Address::create([
                    'city_id' => 5,
                    'area' => $item['AREA_DESCRIPTION'], // Access the property correctly
                ]);
            }

            return response()->json(['message' => 'JSON data stored successfully.']);
        });

    }
}
