<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditDebitNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditDebitNoteController extends Controller
{
    public function index()
    {
        $notes = CreditDebitNote::with('invoice.customer', 'creator')->latest()->get();
        return view('admin.notes.index', compact('notes'));
    }

    public function create()
    {
        $invoices = Invoice::with('customer')->latest()->get();
        return view('admin.notes.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:credit,debit',
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'note_date' => 'required|date',
        ]);

        // Generate unique note number e.g. CDN-YYYYMMDD-XXXX
        $count = CreditDebitNote::whereDate('created_at', now()->toDateString())->count() + 1;
        do {
            $noteNo = 'CDN-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (CreditDebitNote::withTrashed()->where('note_no', $noteNo)->exists());

        CreditDebitNote::create([
            'note_no' => $noteNo,
            'type' => $validated['type'],
            'invoice_id' => $validated['invoice_id'],
            'amount' => $validated['amount'],
            'tax_amount' => $validated['tax_amount'],
            'reason' => $validated['reason'],
            'note_date' => $validated['note_date'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.notes.index')->with('success', 'Credit/Debit Note generated successfully');
    }

    public function show(CreditDebitNote $note)
    {
        $note->load('invoice.customer', 'creator');
        return view('admin.notes.show', compact('note'));
    }

    public function destroy(CreditDebitNote $note)
    {
        $note->delete();
        return redirect()->route('admin.notes.index')->with('success', 'Note deleted successfully');
    }
}
