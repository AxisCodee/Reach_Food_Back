<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
    }

    public function show()
    {
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(['name' => 'required|unique:areas|max:255']);
        $area = Area::create([
            'name' => $validatedData['name'],
        ]);
        return ResponseHelper::success($area);

    }
}
