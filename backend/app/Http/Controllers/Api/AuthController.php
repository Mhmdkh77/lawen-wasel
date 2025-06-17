<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
            'device_token' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($loginField, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }

        $user->update([
            'device_token' => $request->device_token,
        ]);

        return response()->json([
            'user' => $user->fresh()->load(['driver']),
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $code = rand(100000, 999999);

        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $request->email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Mail::to($request->email)->send(new ResetCodeMail($code));

        return response()->json(['message' => 'Reset code sent.']);
    }

    public function resendResetCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $record = DB::table('password_reset_codes')
            ->where('email', $data['email'])
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Forbidden!'], 403);
        }

        $code = rand(100000, 999999);

        DB::table('password_reset_codes')
            ->where('email', $data['email'])
            ->update([
                'code' => $code,
                'updated_at' => now(),
            ]);

        Mail::to($data['email'])->send(new ResetCodeMail($code));

        return response()->json(['message' => 'Reset code sent.']);
    }


    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
        ]);

        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->update(['verified_at' => now()]);

        return response()->json(['message' => 'Code verified. You can now reset your password.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Code not verified or expired'], 403);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_codes')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful']);
    }
}
