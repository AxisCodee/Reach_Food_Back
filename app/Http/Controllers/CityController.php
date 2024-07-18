<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateCityRequest;
use App\Http\Requests\DeleteBranchRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CitiesWithCountryResource;
use App\Http\Resources\CityResource;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use App\Services\CityServices;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class CityController extends Controller
{
    use HasApiResponse;

    public function __construct(private readonly CityServices $cityServices)
    {
    }

    public function index(): JsonResponse
    {
        return $this->success(
            CityResource::collection(City::query()
                ->with(['country','branches:id,name,city_id', 'admin'])
                ->whereHas('branches')
                ->get())
        );
    }

    public function store(CreateCityRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $city = City::query()
                ->with('country')
                ->findOrFail($data['city_id']);
            if(count($city['branches']) > 0){
                return $this->failed('هذا الفرع موجود بالفعل');
            }

            if(isset($data['admin_id'])){
                $this->cityServices->setAdmin($city['id'], $data['admin_id']);
            }

            $this->cityServices->insertBranchesInto($city['id'], $data['branches']);

            $city->load('branches:id,name,city_id');
            return $this->success(CityResource::make(
                $city
            ));
        });

    }

    public function citiesWithoutAdmin(): JsonResponse
    {
        $idsWithAdmins = City::query()->whereHas('admin')->pluck('id')->toArray();
        return $this->success(
            CitiesWithCountryResource::collection(
                City::query()
                    ->with('country')
                    ->whereNotIn('id', $idsWithAdmins)
                    ->get()
            )
        );
    }

    public function citiesWithoutBranches(Request $request): JsonResponse
    {
        $idsWithBranches = City::query()
            ->whereHas('branches')
            ->pluck('id')
            ->toArray();
        return $this->success(
            City::query()
                ->select('id', 'name')
                ->whereNotIn('id', $idsWithBranches)
                ->where('country_id', '=', $request->input('country_id'))
                ->get()
        );
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $data = $request->validated();
        return DB::transaction(function () use ($city, $data) {
            $this->cityServices->updateBranches($city['id'], $data['branches']);
            $this->cityServices->updateAdmin($city['id'], $data['admin_id']);
            $city->load('branches:id,name,city_id');
            return $this->success(CityResource::make($city));
        });
    }

    public function delete(City $city)
    {
        $this->cityServices->deleteOldAdmin($city['id']);
        $branches = $city['branches'];
        $this->cityServices->deleteBranches($branches);
        return $this->success(null);
    }
}
