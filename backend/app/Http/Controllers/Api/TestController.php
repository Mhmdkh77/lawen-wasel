<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationCodeMail;

class TestController extends Controller
{
    public function index()
    {
        Mail::to("killer.m.khalife@gmail.com")->send(new EmailVerificationCodeMail("aaaa"));
        return response()->json(['message' => 'Email sent']);
    }
}
