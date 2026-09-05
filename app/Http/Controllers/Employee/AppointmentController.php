<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('customer', 'assignee', 'technician')
            ->where(function ($q) {
                $q->where('assigned_to', auth()->id())
                  ->orWhere('assigned_technician', auth()->id());
            })
            ->latest()
            ->get();

        return view('employee.appointments.index', compact('appointments'));
    }

    public function submitResponse(Request $request, Appointment $appointment)
    {
        // Verify this employee is assigned
        if ($appointment->assigned_to !== auth()->id() && $appointment->assigned_technician !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'appointment_status' => 'required|in:confirmed,visited,converted,denied',
            'notes'              => 'nullable|string',
            'follow_up_note'     => 'nullable|string',
            'follow_up_date'     => 'nullable|date',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Response submitted successfully!');
    }
}
