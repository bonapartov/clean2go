<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sends a chat/booking push from one app user to another's device.
 *
 * Before 2026-10-13 the apps signed FCM requests themselves with the
 * Firebase service account they fetched from the public /api/settings,
 * which let anyone send push as Clean2Go. Now the key stays on the server
 * (Helpers::getFirebaseServiceAccount) and the apps call this endpoint
 * with the same {"message": {...}} body they used to send to FCM.
 */
class PushNotificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'array'],
            'message.token' => ['required', 'string', 'max:4096'],
            'message.notification' => ['nullable', 'array'],
            'message.notification.title' => ['nullable', 'string', 'max:500'],
            'message.notification.body' => ['nullable', 'string', 'max:2000'],
            'message.data' => ['nullable', 'array', 'max:30'],
        ]);

        $message = ['token' => $validated['message']['token']];

        $notification = array_filter([
            'title' => $validated['message']['notification']['title'] ?? null,
            'body' => $validated['message']['notification']['body'] ?? null,
        ], fn ($value) => $value !== null);
        if ($notification) {
            $message['notification'] = $notification;
        }

        // FCM data values must be strings; the apps send nulls and ids.
        $data = [];
        foreach ($validated['message']['data'] ?? [] as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $data[(string) $key] = mb_substr((string) $value, 0, 1000);
            }
        }
        // The server, not the client, says who sent it.
        $data['senderUserId'] = (string) $request->user()->id;
        $message['data'] = $data;

        if (! Helpers::getFirebaseServiceAccount()) {
            return response()->json([
                'success' => false,
                'message' => __('static.push.not_configured'),
            ], 503);
        }

        if (! Helpers::pushNotification(['message' => $message])) {
            return response()->json([
                'success' => false,
                'message' => __('static.push.send_failed'),
            ], 502);
        }

        return response()->json(['success' => true]);
    }
}
