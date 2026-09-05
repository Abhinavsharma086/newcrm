<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BurnerType;
use Illuminate\Http\Request;

class BurnerTypeController extends Controller
{
    public function index()
    {
        $burnerTypes = BurnerType::orderBy('name')->get();
        return view('admin.burner-types.index', compact('burnerTypes'));
    }

    public function create()
    {
        return view('admin.burner-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:burner_types,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        BurnerType::create($validated);

        return redirect()->route('admin.burner-types.index')->with('success', 'Burner Type added successfully!');
    }

    public function edit(BurnerType $burnerType)
    {
        return view('admin.burner-types.edit', compact('burnerType'));
    }

    public function update(Request $request, BurnerType $burnerType)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:burner_types,name,' . $burnerType->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $burnerType->update($validated);

        return redirect()->route('admin.burner-types.index')->with('success', 'Burner Type updated successfully!');
    }

    public function destroy(BurnerType $burnerType)
    {
        $burnerType->delete();
        return redirect()->route('admin.burner-types.index')->with('success', 'Burner Type deleted successfully!');
    }
}
