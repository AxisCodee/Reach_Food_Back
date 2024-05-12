<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService.
 */
class UserService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function createUserDetails(Request $request)
    {
        return UserDetail::query()->create([
            'image' => $this->fileService->upload($request, 'image'),
            //'address_id' => $request->address_id,
            'location' => $request->location,
            'phone_number' => $request->phone_number
        ]);
    }

}
