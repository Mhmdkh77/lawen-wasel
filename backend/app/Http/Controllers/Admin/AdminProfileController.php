<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('admins.account', ['admin' => $request->user('admin')]);
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $emailChanged = $request->input('email') !== strtolower($admin->email);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'current_password' => [$emailChanged ? 'required' : 'nullable', 'current_password:admin'],
        ]);

        $admin->update(['name' => $data['name'], 'email' => $data['email']]);

        return back()->with('success', 'Account details updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_current' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user('admin')->update(['password' => $data['password']]);
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Password updated. Sign in again.');
    }
}
