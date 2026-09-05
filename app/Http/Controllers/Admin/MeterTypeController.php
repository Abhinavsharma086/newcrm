<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterType;
use Illuminate\Http\Request;

class MeterTypeController extends Controller
{
    public function index()
    {
        $meterTypes = MeterType::orderBy('name')->get();
        return view('admin.meter-types.index', compact('meterTypes'));
    }

    public function create()
    {
        return view('admin.meter-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:meter_types,name',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        MeterType::create($validated);

        return redirect()->route('admin.meter-types.index')->with('success', 'Meter Type added successfully!');
    }

    public function edit(MeterType $meterType)
    {
        return view('admin.meter-types.edit', compact('meterType'));
    }

    public function update(Request $request, MeterType $meterType)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:meter_types,name,' . $meterType->id,
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $meterType->update($validated);

        return redirect()->route('admin.meter-types.index')->with('success', 'Meter Type updated successfully!');
    }

    public function destroy(MeterType $meterType)
    {
        $meterType->delete();
        return redirect()->route('admin.meter-types.index')->with('success', 'Meter Type deleted successfully!');
    }
}
