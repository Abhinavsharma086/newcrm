@extends('layouts.admin')

@section('title', 'Account Details')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Account Details</h5>
        <div>
            <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Account Code:</th>
                        <td>{{ $account->account_code }}</td>
                    </tr>
                    <tr>
                        <th>Account Name:</th>
                        <td>{{ $account->account_name }}</td>
                    </tr>
                    <tr>
                        <th>Account Type:</th>
                        <td><span class="badge bg-primary">{{ ucfirst($account->account_type) }}</span></td>
                    </tr>
                    <tr>
                        <th>Parent Account:</th>
                        <td>{{ $account->parent ? $account->parent->account_name : 'None' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Opening Balance:</th>
                        <td>₹{{ number_format($account->opening_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Current Balance:</th>
                        <td><strong>₹{{ number_format($account->current_balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $account->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $account->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Transaction History</h5>
    </div>
    <div class="card-body">
        @if($account->journalEntryLines->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entry No</th>
                        <th>Narration</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = $account->opening_balance; @endphp
                    @foreach($account->journalEntryLines as $line)
                    @php
                        if (in_array($account->account_type, ['asset', 'expense'])) {
                            $runningBalance += ($line->debit - $line->credit);
                        } else {
                            $runningBalance += ($line->credit - $line->debit);
                        }
                    @endphp
                    <tr>
                        <td>{{ $line->journalEntry->date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.accounts.journal.show', $line->journalEntry) }}">
                                {{ $line->journalEntry->entry_no }}
                            </a>
                        </td>
                        <td>{{ $line->journalEntry->narration }}</td>
                        <td class="text-end">{{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '-' }}</td>
                        <td class="text-end">{{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '-' }}</td>
                        <td class="text-end"><strong>₹{{ number_format($runningBalance, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4">No transactions found for this account.</p>
        @endif
    </div>
</div>
@endsection
