<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FirebaseService
{
    protected $endpoint = 'https://fcm.googleapis.com/v1/projects/YOUR_PROJECT_ID/messages:send';
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $clientEmail = config('firebase.client_email');
        $privateKey = str_replace("\\n", "\n", config('firebase.private_key'));
        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $claimSet = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUrl,
            'iat' => $now,
            'exp' => $now + 3600,
        ];
        $jwtClaim = base64_encode(json_encode($claimSet));
        openssl_sign("$jwtHeader.$jwtClaim", $signature, $privateKey, 'sha256WithRSAEncryption');
        $jwt = "$jwtHeader.$jwtClaim." . base64_encode($signature);

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response['access_token'];
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ]
            ]
        ];

        $response = Http::withToken($this->accessToken)
            ->post($this->endpoint, $payload);

        return $response->json();
    }
}
