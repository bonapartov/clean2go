<?php

namespace App\Support;

/**
 * Agora RTC token (AccessToken "006"), built on the server so the Agora
 * certificate never leaves it. Before 2026-10-13 the app fetched the
 * certificate from the public /api/settings and sent it to a Cloud Function.
 *
 * Byte-for-byte port of RtcTokenBuilder.buildTokenWithUid from the official
 * npm package agora-access-token@2.0.4 (src/AccessToken.js); verified
 * against it with fixed salt/ts in tests/Feature/AgoraTokenTest.php.
 *
 * Own code (not Fixit).
 */
class AgoraRtcToken
{
    private const VERSION = '006';

    private const JOIN_CHANNEL = 1;

    private const PUBLISH_AUDIO = 2;

    private const PUBLISH_VIDEO = 3;

    private const PUBLISH_DATA = 4;

    /** Publisher token: join + publish audio/video/data until $expireTs. */
    public static function publisher(
        string $appId,
        string $appCertificate,
        string $channelName,
        int $uid,
        int $expireTs,
        ?int $salt = null,
        ?int $ts = null
    ): string {
        $uidStr = $uid === 0 ? '' : (string) $uid;
        $salt ??= random_int(1, 0xFFFFFFFF);
        $ts ??= time() + 24 * 3600;

        $privileges = [
            self::JOIN_CHANNEL => $expireTs,
            self::PUBLISH_AUDIO => $expireTs,
            self::PUBLISH_VIDEO => $expireTs,
            self::PUBLISH_DATA => $expireTs,
        ];

        $message = pack('V', $salt).pack('V', $ts).pack('v', count($privileges));
        foreach ($privileges as $key => $expire) {
            $message .= pack('v', $key).pack('V', $expire);
        }

        $signature = hash_hmac('sha256', $appId.$channelName.$uidStr.$message, $appCertificate, true);

        $content = self::packString($signature)
            .pack('V', crc32($channelName))
            .pack('V', crc32($uidStr))
            .self::packString($message);

        return self::VERSION.$appId.base64_encode($content);
    }

    private static function packString(string $bytes): string
    {
        return pack('v', strlen($bytes)).$bytes;
    }
}
