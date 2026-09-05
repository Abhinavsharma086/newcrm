<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::with('creator')->latest()->get();
        return view('admin.journal.index', compact('entries'));
    }

    public function create()
    {
        $accounts = Account::all();
        $entryNo = $this->generateEntryNumber();
        return view('admin.journal.create', compact('accounts', 'entryNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'narration' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // Validate double-entry
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if ($totalDebit != $totalCredit) {
            return back()->withErrors(['lines' => 'Total debits must equal total credits'])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $entry = JournalEntry::create([
                'entry_no' => $this->generateEntryNumber(),
                'date' => $validated['date'],
                'narration' => $validated['narration'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $line) {
                $entry->lines()->create($line);

                // Update account balance
                $account = Account::find($line['account_id']);
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $account->current_balance += ($line['debit'] - $line['credit']);
                } else {
                    $account->current_balance += ($line['credit'] - $line['debit']);
                }
                $account->save();
            }
        });

        return redirect()->route('admin.accounts.journal.index')->with('success', 'Journal entry created successfully');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account', 'creator');
        return view('admin.journal.show', ['journal' => $journalEntry]);
    }

    public function destroy(JournalEntry $journalEntry)
    {
        $journalEntry->delete();
        return redirect()->route('admin.accounts.journal.index')->with('success', 'Journal entry deleted successfully');
    }

    private function generateEntryNumber()
    {
        $year = date('Y');
        $prefix = "JE-{$year}-";
        
        $lastEntry = JournalEntry::where('entry_no', 'like', $prefix . '%')
            ->orderBy('entry_no', 'desc')
            ->first();
        
        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
