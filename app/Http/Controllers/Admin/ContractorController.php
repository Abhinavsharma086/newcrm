<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::orderBy('name')->get();
        return view('admin.contractors.index', compact('contractors'));
    }

    public function create()
    {
        return view('admin.contractors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:contractors,name',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'type'           => 'required|in:rfc,jmr,general',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        Contractor::create($validated);

        return redirect()->route('admin.contractors.index')
            ->with('success', 'Contractor added successfully!');
    }

    public function edit(Contractor $contractor)
    {
        return view('admin.contractors.edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:contractors,name,' . $contractor->id,
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'type'           => 'required|in:rfc,jmr,general',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $contractor->update($validated);

        return redirect()->route('admin.contractors.index')
            ->with('success', 'Contractor updated successfully!');
    }

    public function destroy(Contractor $contractor)
    {
        $contractor->delete();
        return redirect()->route('admin.contractors.index')
            ->with('success', 'Contractor deleted successfully!');
    }
}
