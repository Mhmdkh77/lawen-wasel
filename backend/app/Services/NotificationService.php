<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotificationService
{

    public function sendPush($to, $title, $body, $data = [])
    {
        $SERVER_KEY = env('FIREBASE_SERVER_KEY');

        return Http::withHeaders([
            'Authorization' => 'key=' . $SERVER_KEY,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $to, // device_token
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }
}
