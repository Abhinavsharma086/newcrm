<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->get('section', 'registration');
        if (!in_array($section, ['registration', 'technical'])) {
            $section = 'registration';
        }
        
        $customers = Customer::where('assigned_to', auth()->id())->latest()->get();
        return view('employee.customers.index', compact('customers', 'section'));
    }

    public function show(Customer $customer)
    {
        if ($customer->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized access to this customer.');
        }
        $customer->load('invoices', 'leads', 'tickets');
        return view('employee.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if ($customer->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized access to this customer.');
        }
        return view('employee.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized access to this customer.');
        }
        
        $validated = $request->validate([
            'meter_no' => 'nullable|string|max:100',
            'meter_type' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'rfc_contractor' => 'nullable|string|max:100',
            'rfc_date' => 'nullable|date',
            'jmr_contractor' => 'nullable|string|max:100',
            'jmr_date' => 'nullable|date',
            'lmc_date' => 'nullable|date',
            'burner_type' => 'nullable|string|max:100',
            'job_card' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'photo' => 'nullable|image|max:2048',
            'inside_kitchen_photo' => 'nullable|image|max:2048',
            'meter_photo_3_angles' => 'nullable|image|max:2048',
            'outside_kitchen_photo' => 'nullable|image|max:2048',
            'rfc_report_image' => 'nullable|image|max:2048',
            'jmr_report_image' => 'nullable|image|max:2048',
            'remarks' => 'nullable|string',
            'primary_id_number' => 'nullable|string|max:100',
            'primary_id_file' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'secondary_id_type' => 'nullable|string|max:100',
            'secondary_id_number' => 'nullable|string|max:100',
            'secondary_id_file' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'passport_photo' => 'nullable|image|max:2048',
            'address_proof_file' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('job_card')) {
            $validated['job_card'] = $request->file('job_card')->store('job_cards', 'public');
        }
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }
        if ($request->hasFile('inside_kitchen_photo')) {
            $validated['inside_kitchen_photo'] = $request->file('inside_kitchen_photo')->store('photos/kitchen_inside', 'public');
        }
        if ($request->hasFile('meter_photo_3_angles')) {
            $validated['meter_photo_3_angles'] = $request->file('meter_photo_3_angles')->store('photos/meter', 'public');
        }
        if ($request->hasFile('outside_kitchen_photo')) {
            $validated['outside_kitchen_photo'] = $request->file('outside_kitchen_photo')->store('photos/kitchen_outside', 'public');
        }
        if ($request->hasFile('rfc_report_image')) {
            $validated['rfc_report_image'] = $request->file('rfc_report_image')->store('photos/rfc_reports', 'public');
        }
        if ($request->hasFile('jmr_report_image')) {
            $validated['jmr_report_image'] = $request->file('jmr_report_image')->store('photos/jmr_reports', 'public');
        }
        if ($request->hasFile('primary_id_file')) {
            $validated['primary_id_file'] = $request->file('primary_id_file')->store('kyc/primary', 'public');
        }
        if ($request->hasFile('secondary_id_file')) {
            $validated['secondary_id_file'] = $request->file('secondary_id_file')->store('kyc/secondary', 'public');
        }
        if ($request->hasFile('passport_photo')) {
            $validated['passport_photo'] = $request->file('passport_photo')->store('kyc/passport', 'public');
        }
        if ($request->hasFile('address_proof_file')) {
            $validated['address_proof_file'] = $request->file('address_proof_file')->store('kyc/address', 'public');
        }

        $customer->update($validated);

        $section = $request->get('section', 'registration');
        return redirect()->route('employee.customers.index', ['section' => $section])->with('success', 'Customer details updated successfully');
    }
}
