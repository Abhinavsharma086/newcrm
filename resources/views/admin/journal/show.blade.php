@extends('layouts.admin')

@section('title', 'Journal Entry Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Journal Entry Details</h5>
        <div>
            <a href="{{ route('admin.accounts.journal.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Entry No:</th>
                        <td>{{ $journal->entry_no }}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $journal->date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td>{{ $journal->creator->name }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Narration:</th>
                        <td>{{ $journal->narration }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $journal->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <h6 class="mb-3">Entry Lines</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    @foreach($journal->lines as $line)
                    @php
                        $totalDebit += $line->debit;
                        $totalCredit += $line->credit;
                    @endphp
                    <tr>
                        <td>{{ $line->account->account_code }}</td>
                        <td>
                            <a href="{{ route('admin.accounts.show', $line->account) }}">
                                {{ $line->account->account_name }}
                            </a>
                        </td>
                        <td class="text-end">{{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '-' }}</td>
                        <td class="text-end">{{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end">₹{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">₹{{ number_format($totalCredit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($totalDebit == $totalCredit)
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Entry is balanced
        </div>
        @else
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> Entry is NOT balanced
        </div>
        @endif
    </div>
</div>
@endsection
