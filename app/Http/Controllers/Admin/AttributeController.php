<?php
// FILE: app/Http/Controllers/Admin/AttributeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        // Группируем опции по типу (size, product_type)
        $attributes = AttributeOption::all()->groupBy('type');
        return view('admin.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:size,product_type,material',
            'value' => 'required|string|max:255',
        ]);

        AttributeOption::firstOrCreate(
            ['type' => $validated['type'], 'value' => $validated['value']],
            ['slug' => Str::slug($validated['value'])]
        );

        return back()->with('success', 'Attribute option added.');
    }

    public function destroy($id)
    {
        AttributeOption::destroy($id);
        return back()->with('success', 'Attribute option deleted.');
    }
}