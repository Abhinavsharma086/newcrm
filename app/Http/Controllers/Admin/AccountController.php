<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::whereNull('parent_id')->with('children')->get();
        return view('admin.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parentAccounts = Account::all();
        return view('admin.accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'required|numeric',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];

        Account::create($validated);

        return redirect()->route('admin.accounts.index')->with('success', 'Account created successfully');
    }

    public function show(Account $account)
    {
        $account->load('journalEntryLines.journalEntry');
        return view('admin.accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        $parentAccounts = Account::where('id', '!=', $account->id)->get();
        return view('admin.accounts.edit', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|unique:accounts,account_code,' . $account->id,
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
        ]);

        $account->update($validated);

        return redirect()->route('admin.accounts.index')->with('success', 'Account updated successfully');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('admin.accounts.index')->with('success', 'Account deleted successfully');
    }
}
