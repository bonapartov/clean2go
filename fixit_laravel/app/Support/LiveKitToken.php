<?php

namespace App\Support;

/**
 * LiveKit access token: a JWT (HS256) signed with the API secret of our
 * LiveKit server (infra/livekit). Grants one user access to one room.
 * Format: https://docs.livekit.io/home/get-started/authentication/
 *
 * Own code (not Fixit).
 */
class LiveKitToken
{
    public static function forRoom(
        string $apiKey,
        string $apiSecret,
        string $room,
        string $identity,
        string $name,
        int $ttlSeconds = 3600
    ): string {
        $now = time();
        $payload = [
            'iss' => $apiKey,
            'sub' => $identity,
            'name' => $name,
            'nbf' => $now - 10,
            'exp' => $now + $ttlSeconds,
            'video' => [
                'room' => $room,
                'roomJoin' => true,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
            ],
        ];

        $segments = [
            self::b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT'])),
            self::b64(json_encode($payload, JSON_UNESCAPED_UNICODE)),
        ];
        $segments[] = self::b64(hash_hmac('sha256', implode('.', $segments), $apiSecret, true));

        return implode('.', $segments);
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
