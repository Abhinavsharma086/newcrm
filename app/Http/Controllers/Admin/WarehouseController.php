<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('manager')->get();
        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $managers = User::where('status', 'active')->get();
        return view('admin.warehouses.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Warehouse::create($validated);

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse created successfully');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('manager', 'stockTransactions.product');
        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $managers = User::where('status', 'active')->get();
        return view('admin.warehouses.edit', compact('warehouse', 'managers'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse updated successfully');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse deleted successfully');
    }
}
