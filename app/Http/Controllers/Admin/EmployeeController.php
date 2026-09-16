<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with('roles')->withCount('assignedTasks')->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,name',
            'assigned_societies' => 'nullable|string',
        ]);

        $employee = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'assigned_societies' => $validated['assigned_societies'] ?? null,
        ]);

        $employee->assignRole($validated['role']);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully');
    }

    public function show(User $employee)
    {
        $employee->load('roles', 'permissions', 'assignedTasks', 'assignedTickets');
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(User $employee)
    {
        $roles = Role::all();
        return view('admin.employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|min:8|confirmed',
            'assigned_societies' => 'nullable|string',
        ]);

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'status' => $validated['status'],
            'assigned_societies' => $validated['assigned_societies'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $employee->update(['password' => Hash::make($validated['password'])]);
        }

        $employee->syncRoles([$validated['role']]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(User $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee deactivated successfully');
    }
}
