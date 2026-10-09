<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $admins = Admin::orderBy('name')->paginate(20);

        return view('admins.index', compact('admins'));
    }

    public function show(Admin $admin): View
    {
        return view('admins.show', compact('admin'));
    }

    public function create(): View
    {
        return view('admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_super_admin' => ['sometimes', 'boolean'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        return redirect()->route('admin.admins.show', $admin)->with('success', 'Admin account created.');
    }

    public function toggleStatus(Request $request, Admin $admin): RedirectResponse
    {
        abort_if($admin->is($request->user('admin')), 403, 'You cannot disable your own account.');

        DB::transaction(function () use ($admin) {
            $admin->refresh();
            if ($admin->is_active) {
                abort_if(Admin::where('is_active', true)->count() <= 1, 422, 'The last active admin cannot be disabled.');
                abort_if($admin->is_super_admin && Admin::where('is_active', true)->where('is_super_admin', true)->count() <= 1,
                    422, 'The last active super admin cannot be disabled.');
            }

            $admin->update(['is_active' => ! $admin->is_active]);
        });

        return back()->with('success', $admin->is_active ? 'Admin account enabled.' : 'Admin account disabled.');
    }

    public function toggleRole(Request $request, Admin $admin): RedirectResponse
    {
        abort_if($admin->is($request->user('admin')), 403, 'You cannot change your own role.');

        DB::transaction(function () use ($admin) {
            $admin->refresh();
            abort_if($admin->is_active && $admin->is_super_admin
                && Admin::where('is_active', true)->where('is_super_admin', true)->count() <= 1,
                422, 'The last active super admin cannot be demoted.');

            $admin->update(['is_super_admin' => ! $admin->is_super_admin]);
        });

        return back()->with('success', $admin->is_super_admin ? 'Super admin access granted.' : 'Super admin access removed.');
    }
}
