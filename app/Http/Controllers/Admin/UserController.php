<?php
// FILE: app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        // 1. БЕЗОПАСНОСТЬ: Только Супер-Админ имеет доступ к этому контроллеру
        // Если обычный менеджер попытается зайти -> 403 Forbidden
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'super_admin') {
                abort(403, 'Access denied. Only Super Admin can manage users.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::orderBy('id')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:manager,super_admin',
            // Если роль менеджер - tenant_id обязателен, если админ - nullable
            'tenant_id' => 'required_if:role,manager',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:manager,super_admin',
            'tenant_id' => 'required_if:role,manager',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        if ($id == auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        User::destroy($id);
        return back()->with('success', 'User deleted.');
    }
}