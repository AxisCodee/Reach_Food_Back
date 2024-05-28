<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $result = Permission::query()->get()->toArray();
        return ResponseHelper::success($result);
    }
}
