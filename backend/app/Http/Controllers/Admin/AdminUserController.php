<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('name')->paginate(20);

        return view('admins.index', compact('admins'));
    }

    public function show(Admin $admin)
    {
        return view('admins.show', compact('admin'));
    }
}
