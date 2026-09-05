<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::where('assigned_to', auth()->id())
            ->with('customer')
            ->latest()
            ->get();

        return view('employee.leads.index', compact('leads'));
    }

    public function show(Lead $lead)
    {
        if ($lead->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $lead->load('customer');
        return view('employee.leads.show', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        if ($lead->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'stage' => 'required|in:new,contacted,qualified,converted,lost',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $lead->update($validated);

        return back()->with('success', 'Lead updated successfully');
    }
}
