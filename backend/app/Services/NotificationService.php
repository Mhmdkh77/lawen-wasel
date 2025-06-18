<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotificationService
{

    public function sendPush($to, $title, $body, $data = [])
    {
        $SERVER_KEY = env('FIREBASE_SERVER_KEY');

        if (!$SERVER_KEY) {
            throw new \Exception('Firebase server key not set.');
        }

        $payload = [
            'to' => $to,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        return Http::withHeaders([
            'Authorization' => 'key=' . $SERVER_KEY,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }
}
