<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('customer', 'assignee')->latest()->get();
        return view('admin.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $customers = Customer::all()->unique('name');
        $employees = User::where('status', 'active')->get();
        $ticketNo = 'TKT-' . date('Ymd') . '-' . str_pad(Ticket::count() + 1, 4, '0', STR_PAD_LEFT);
        
        return view('admin.tickets.create', compact('customers', 'employees', 'ticketNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
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

        $validated['ticket_no'] = 'TKT-' . date('Ymd') . '-' . str_pad(Ticket::count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['created_by'] = auth()->id();
        
        // Calculate SLA based on priority
        $slaHours = match($validated['priority']) {
            'urgent' => 4,
            'high' => 24,
            'medium' => 48,
            'low' => 72,
        };
        $validated['sla_due_at'] = now()->addHours($slaHours);

        Ticket::create($validated);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket created successfully');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load('customer', 'assignee', 'creator', 'comments.user');
        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $customers = Customer::all()->unique('name');
        $employees = User::where('status', 'active')->get();
        return view('admin.tickets.edit', compact('ticket', 'customers', 'employees'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
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

        if ($validated['status'] === 'resolved' && $ticket->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        if ($validated['status'] === 'closed' && $ticket->status !== 'closed') {
            $validated['closed_at'] = now();
        }

        $ticket->update($validated);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted successfully');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Comment added successfully');
    }
}
