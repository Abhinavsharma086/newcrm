<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Society;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointment::with('customer', 'assignee', 'technician')->latest()->get();
        
        // Technicians and Marketing employees
        $employees = User::where('status', 'active')->get();
        $societies = Society::orderBy('name')->get();
        
        // AI Smart Grouping & Recommendations
        $allCustomers = Customer::whereNotNull('society')->where('society', '!=', '')->get();
        $aiGroups = [];
        
        foreach ($societies as $soc) {
            $socCustomers = $allCustomers->where('society', $soc->name);
            $totalCount = $socCustomers->count();
            
            if ($totalCount > 0) {
                // Find customers without active appointments
                $pendingCustomers = [];
                foreach ($socCustomers as $cust) {
                    $hasActive = Appointment::where('customer_id', $cust->id)
                        ->whereNotIn('appointment_status', ['converted', 'denied'])
                        ->exists();
                    if (!$hasActive) {
                        $pendingCustomers[] = $cust;
                    }
                }
                
                $pendingCount = count($pendingCustomers);
                if ($pendingCount > 0) {
                    $aiGroups[] = [
                        'society' => $soc->name,
                        'total_customers' => $totalCount,
                        'pending_count' => $pendingCount,
                        'customers' => $pendingCustomers
                    ];
                }
            }
        }
        
        // Sort by highest pending count first
        usort($aiGroups, function($a, $b) {
            return $b['pending_count'] <=> $a['pending_count'];
        });

        return view('admin.appointments.index', compact('appointments', 'employees', 'aiGroups', 'societies'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $employees = User::where('status', 'active')->get();
        return view('admin.appointments.create', compact('customers', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'assigned_to'         => 'nullable|exists:users,id',
            'assigned_technician' => 'nullable|exists:users,id',
            'title'               => 'required|string|max:255',
            'appointment_status'  => 'required|in:scheduled,confirmed,visited,converted,denied,rescheduled',
            'source'              => 'required|string|max:100',
            'notes'               => 'nullable|string',
            'follow_up_date'      => 'nullable|date',
            'follow_up_note'      => 'nullable|string',
            'appointment_date'    => 'required|date',
            'appointment_time'    => 'required',
        ]);

        $customer = Customer::find($validated['customer_id']);
        $validated['society'] = $customer->society;
        $validated['stage'] = 'new';

        Appointment::create($validated);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment scheduled successfully');
    }

    public function bulkSchedule(Request $request)
    {
        $validated = $request->validate([
            'society'             => 'required|string',
            'appointment_date'    => 'required|date',
            'appointment_time'    => 'required',
            'assigned_to'         => 'required|exists:users,id', // Marketing/Creator
            'assigned_technician' => 'nullable|exists:users,id', // Field Technician
        ]);
        
        $customers = Customer::where('society', $validated['society'])->get();
        $count = 0;
        
        foreach ($customers as $cust) {
            $hasActive = Appointment::where('customer_id', $cust->id)
                ->whereNotIn('appointment_status', ['converted', 'denied'])
                ->exists();
                
            if (!$hasActive) {
                Appointment::create([
                    'customer_id'         => $cust->id,
                    'society'             => $validated['society'],
                    'assigned_to'         => $validated['assigned_to'],
                    'assigned_technician' => $validated['assigned_technician'],
                    'title'               => 'Visit to: ' . $validated['society'] . ' - ' . $cust->name,
                    'stage'               => 'new',
                    'appointment_status'  => 'scheduled',
                    'source'              => 'AI Grouping',
                    'notes'               => 'Bulk Appointment Scheduled by AI Grouping',
                    'appointment_date'    => $validated['appointment_date'],
                    'appointment_time'    => $validated['appointment_time']
                ]);
                $count++;
            }
        }
        
        return redirect()->route('admin.appointments.index')->with('success', "AI Smart Scheduler matched and scheduled $count appointments for {$validated['society']}");
    }

    public function show(Appointment $appointment)
    {
        $appointment->load('customer', 'assignee', 'technician');
        return view('admin.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $customers = Customer::orderBy('name')->get();
        $employees = User::where('status', 'active')->get();
        return view('admin.appointments.edit', compact('appointment', 'customers', 'employees'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'burner_type'           => 'required|in:Hob Stove,Normal',
            'assigned_to'           => 'nullable|exists:users,id',
            'assigned_technician'   => 'nullable|exists:users,id',
            'title'                 => 'required|string|max:255',
            'appointment_status'    => 'required|in:scheduled,confirmed,visited,converted,denied,rescheduled',
            'source'                => 'required|string|max:100',
            'notes'                 => 'nullable|string',
            'follow_up_date'        => 'nullable|date',
            'follow_up_note'        => 'nullable|string',
            'appointment_date'      => 'required|date',
            'appointment_time'      => 'required',
            'kitchen_burner_video'  => 'nullable|file|mimes:mp4,mov,avi,qt|max:20480', // 20MB limit
            'external_riser_video'  => 'nullable|file|mimes:mp4,mov,avi,qt|max:20480',
        ]);

        if ($request->hasFile('kitchen_burner_video')) {
            $validated['kitchen_burner_video'] = $request->file('kitchen_burner_video')->store('appointments/videos', 'public');
        }
        if ($request->hasFile('external_riser_video')) {
            $validated['external_riser_video'] = $request->file('external_riser_video')->store('appointments/videos', 'public');
        }

        $customer = Customer::find($validated['customer_id']);
        $validated['society'] = $customer->society;

        $appointment->update($validated);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment details updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment deleted successfully');
    }

    public function deny(Request $request, Appointment $appointment)
    {
        $request->validate([
            'denial_reason'       => 'required|string',
            'denied_by_person'    => 'required|string|max:255',
            'further_action'      => 'required|in:reschedule,drop,callback,escalate',
            'further_action_date' => 'nullable|date',
            'denial_photo'        => 'nullable|image|max:2048',
        ]);

        $data = [
            'appointment_status'  => 'denied',
            'denial_reason'       => $request->denial_reason,
            'denied_by_person'    => $request->denied_by_person,
            'further_action'      => $request->further_action,
            'further_action_date' => $request->further_action_date,
        ];

        if ($request->hasFile('denial_photo')) {
            $path = $request->file('denial_photo')->store('denial_photos', 'public');
            $data['denial_photo'] = $path;
        }

        $appointment->update($data);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment marked as DENIED.');
    }

    public function reopen(Appointment $appointment)
    {
        $appointment->update([
            'appointment_status'  => 'scheduled',
            'denial_reason'       => null,
            'denied_by_person'    => null,
            'denial_photo'        => null,
            'further_action'      => null,
            'further_action_date' => null,
        ]);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment reopened successfully.');
    }

    public function quickReschedule(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date'    => 'required|date',
            'appointment_time'    => 'required',
            'assigned_to'         => 'nullable|exists:users,id',
            'assigned_technician' => 'nullable|exists:users,id',
            'notes'               => 'nullable|string',
        ]);

        $validated['appointment_status'] = 'rescheduled';

        $appointment->update($validated);

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment rescheduled successfully.');
    }
}
