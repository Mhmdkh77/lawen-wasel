<?php

namespace App\Services;

class NotificationService
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendPush($to, $title, $body, $data = [])
    {
        return $this->firebaseService->sendNotification($to, $title, $body, $data);
    }
}
