<?php

//  File (app/Http/Controllers/Admin/ManagerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::where('role', 'manager')->latest()->get();
        return view('admin.managers.index', compact('managers'));
    }

    public function create()
    {
        $tenants = config('tenants.tenants');
        // Используем единую форму. Переменная $manager не передается, шаблон поймет, что это создание.
        return view('admin.managers.form', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'tenant_id' => 'required|string',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'manager',
            'tenant_id' => $validated['tenant_id'],
        ]);

        return redirect()->route('admin.managers.index')->with('success', 'Manager created successfully.');
    }

    public function edit($id)
    {
        $manager = User::where('role', 'manager')->findOrFail($id);
        $tenants = config('tenants.tenants');
        // Используем ту же форму, но передаем $manager для редактирования
        return view('admin.managers.form', compact('manager', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $manager = User::where('role', 'manager')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($manager->id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'tenant_id' => 'required|string',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'tenant_id' => $validated['tenant_id'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $manager->update($data);

        return redirect()->route('admin.managers.index')->with('success', 'Manager updated successfully.');
    }

    public function destroy($id)
    {
        $manager = User::where('role', 'manager')->findOrFail($id);
        $manager->delete();

        return back()->with('success', 'Manager deleted.');
    }
}