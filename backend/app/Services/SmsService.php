<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }


    public function send(string $to, string $message)
    {
        return $this->twilio->messages->create($to, [
            'from' => config('services.twilio.number'),
            'body' => $message,
        ]);
    }
}
