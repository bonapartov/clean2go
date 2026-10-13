<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Support\AgoraRtcToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Agora call token for an app user, signed on the server: the certificate
 * stays in admin settings (agora.app_id / agora.certificate) and is no
 * longer exposed by /api/settings. Replaces the generateToken Cloud
 * Function, which took the certificate from the client. Response shape
 * matches that function: {"data": {"token", "channelName"}}.
 */
class AgoraTokenController extends Controller
{
    private const TTL_SECONDS = 3600;

    public function store(): JsonResponse
    {
        $agora = Helpers::getSettings()['agora'] ?? [];
        $appId = (string) ($agora['app_id'] ?? '');
        $certificate = (string) ($agora['certificate'] ?? '');

        if ($appId === '' || $certificate === '') {
            return response()->json([
                'success' => false,
                'message' => __('static.agora.not_configured'),
            ], 503);
        }

        // Unique per call; the old function used a random 0–99 number,
        // so two calls could land in the same channel.
        $channelName = 'c2g_'.Str::lower(Str::random(20));

        return response()->json([
            'success' => true,
            'data' => [
                'token' => AgoraRtcToken::publisher($appId, $certificate, $channelName, 0, time() + self::TTL_SECONDS),
                'channelName' => $channelName,
            ],
        ]);
    }
}
