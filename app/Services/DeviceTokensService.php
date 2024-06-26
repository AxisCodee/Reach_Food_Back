<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DeviceTokensService
{
    public function create(int $accessTokenId, string $token): void
    {
        DB::table('device_tokens')
            ->insert([
                'access_token_id' => $accessTokenId,
                'token' => $token,
            ]);
    }

    public function update(int $prevAccessTokenId, int $newAccessTokenId): void
    {
        DB::table('device_tokens')
            ->where('access_token_id', $prevAccessTokenId)
            ?->update(['access_token_id' => $newAccessTokenId]);
    }

    public function get(array $userIds): array
    {
        return DB::table('personal_access_tokens')
            ->select('device_tokens.token')
            ->whereIn('tokenable_id', $userIds)
            ->join('device_tokens', 'device_tokens.access_token_id', '=', 'personal_access_tokens.id')
            ->pluck('token')
            ->toArray();
    }
}
