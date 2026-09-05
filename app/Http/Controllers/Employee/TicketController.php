<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('assigned_to', auth()->id())
            ->with('customer')
            ->latest()
            ->get();

        return view('employee.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->load('customer', 'creator', 'comments.user');
        return view('employee.tickets.show', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:open,progress,resolved,closed',
        ]);

        if ($validated['status'] === 'resolved' && $ticket->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        $ticket->update($validated);

        return back()->with('success', 'Ticket status updated successfully');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        if ($ticket->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return back()->with('success', 'Comment added successfully');
    }
}
