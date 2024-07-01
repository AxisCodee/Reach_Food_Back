<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponse
{
    public function success($data, string $message = 'ok', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public function failed(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $status);
    }

    public function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
