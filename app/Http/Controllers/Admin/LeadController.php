<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('customer', 'assignee')->latest()->get();
        return view('admin.leads.index', compact('leads'));
    }

    public function create()
    {
        $customers = Customer::all();
        $employees = User::where('status', 'active')->get();
        return view('admin.leads.create', compact('customers', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'stage' => 'required|in:new,contacted,qualified,converted,lost',
            'source' => 'required|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $customerId = null;
        if (!empty($validated['customer_name'])) {
            $customer = Customer::firstOrCreate(
                ['name' => $validated['customer_name']],
                ['phone' => '0000000000', 'source' => 'manual']
            );
            $customerId = $customer->id;
        }
        $validated['customer_id'] = $customerId;

        Lead::create($validated);

        return redirect()->route('admin.leads.index')->with('success', 'Lead created successfully');
    }

    public function show(Lead $lead)
    {
        $lead->load('customer', 'assignee');
        return view('admin.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $customers = Customer::all();
        $employees = User::where('status', 'active')->get();
        return view('admin.leads.edit', compact('lead', 'customers', 'employees'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'stage' => 'required|in:new,contacted,qualified,converted,lost',
            'source' => 'required|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $customerId = null;
        if (!empty($validated['customer_name'])) {
            $customer = Customer::firstOrCreate(
                ['name' => $validated['customer_name']],
                ['phone' => '0000000000', 'source' => 'manual']
            );
            $customerId = $customer->id;
        }
        $validated['customer_id'] = $customerId;

        $lead->update($validated);

        return redirect()->route('admin.leads.index')->with('success', 'Lead updated successfully');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted successfully');
    }
}
