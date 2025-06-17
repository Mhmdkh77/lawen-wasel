<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationCodeMail;
use App\Models\Driver;
use App\Models\Passenger;
use App\Services\SmsService;

class RegistrationController extends Controller
{
    public function setLocation(Request $request) {}

    public function start(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:passenger,driver',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:registrations,email|unique:users,email',
            'gender' => 'required|in:male,female',
            'phone' => 'phone'
        ]);

        $registration = Registration::create([
            'role' => $data['role'],
            'name' => $data['name'],
            'email' => $data['email'],
            'gender' => $data['gender'],
            'phone' => $data['phone'],
            'email_verification_code' => rand(100000, 999999),
        ]);

        Mail::to($registration->email)->send(new EmailVerificationCodeMail($registration->email_verification_code));

        return response()->json([
            'registration_id' => $registration->id,
            'message' => 'Verification code sent to email.',
        ]);
    }

    public function resendVerifyEmail(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
        ]);

        $registration = Registration::findOrFail($data['registration_id']);

        $verificationCode = rand(100000, 999999);
        $registration->update(['email_verification_code' => $verificationCode]);

        Mail::to($registration->email)->send(new EmailVerificationCodeMail($verificationCode));

        return response()->json([
            'registration_id' => $registration->id,
            'message' => 'Verification code sent to email.',
        ]);
    }


    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'code' => 'required',
        ]);

        $registration = Registration::find($data['registration_id']);

        if ($registration->email_verification_code !== $data['code']) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $registration->update(['email_verified_at' => now()]);
        return response()->json(['message' => 'Email verified']);
    }

    public function addPhone(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'phone' => 'phone',
        ]);

        $registration = Registration::find($data['registration_id']);


        $registration->update([
            'phone' => $data['phone'],
            'phone_verification_code' => rand(100000, 999999),
        ]);

        $sms = new SmsService();
        $sms->send($registration->phone, "Your verification code is: {$registration->phone_verification_code}");

        return response()->json([
            'registration_id' => $registration->id,
            'message' => 'Verification code sent to email.',
        ]);
    }

    public function verifyPhone(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'code' => 'required',
        ]);

        $registration = Registration::find($data['registration_id']);

        if ($registration->phone_verification_code !== $data['code']) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $registration->update(['phone_verified_at' => now()]);
        return response()->json(['message' => 'Phone verified']);
    }

    public function finalize(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'password' => 'required|min:6|confirmed',
        ]);

        $registration = Registration::find($data['registration_id']);

        if (!$registration->email_verified_at
            // || !$registration->phone_verified_at
        ) {
            return response()->json(['message' => 'Register Failed, Email Not Verified'], 422);
        }

        $user = User::create([
            'name' => $registration->name,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'role' => $registration->role,
            'gender' => $registration->gender,
            'password' => bcrypt($data['password']),
            'email_verified_at' => now(),
            'phone_verified_at' => now()
        ]);

        if ($registration->role === 'passenger') {
            Passenger::create(['user_id' => $user->id]);
        } elseif ($registration->role === 'driver') {
            Driver::create([
                'user_id' => $user->id,
                'is_verified' => false,
            ]);
        }

        $registration->delete();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken
        ]);
    }
}
