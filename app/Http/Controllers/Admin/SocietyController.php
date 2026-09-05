<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use Illuminate\Http\Request;

class SocietyController extends Controller
{
    public function index()
    {
        $societies = Society::with('contractor')->orderBy('name')->get();
        return view('admin.societies.index', compact('societies'));
    }

    public function create()
    {
        $contractors = \App\Models\Contractor::where('is_active', true)->orderBy('name')->get();
        return view('admin.societies.create', compact('contractors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:societies,name',
            'contractor_id' => 'nullable|exists:contractors,id',
        ]);

        Society::create($validated);

        return redirect()->route('admin.societies.index')->with('success', 'Society created successfully');
    }

    public function edit(Society $society)
    {
        $contractors = \App\Models\Contractor::where('is_active', true)->orderBy('name')->get();
        return view('admin.societies.edit', compact('society', 'contractors'));
    }

    public function update(Request $request, Society $society)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:societies,name,' . $society->id,
            'contractor_id' => 'nullable|exists:contractors,id',
        ]);

        $society->update($validated);

        return redirect()->route('admin.societies.index')->with('success', 'Society updated successfully');
    }

    public function destroy(Society $society)
    {
        $society->delete();
        return redirect()->route('admin.societies.index')->with('success', 'Society deleted successfully');
    }
}
