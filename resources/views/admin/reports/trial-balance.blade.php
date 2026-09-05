@extends('layouts.app')

@section('title', 'Trial Balance')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Trial Balance</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Trial Balance Report</h2>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card>
                <div class="text-center mb-4">
                    <h4>HisabMittra Trial Balance</h4>
                    <p class="text-muted">As of {{ now()->format('d F Y') }}</p>
                </div>
                
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th class="text-end">Debit (₹)</th>
                            <th class="text-end">Credit (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                        <tr>
                            <td><code>{{ $account->account_code }}</code></td>
                            <td>{{ $account->account_name }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($account->account_type) }}</span></td>
                            <td class="text-end">
                                {{ $account->debit > 0 ? number_format($account->debit, 2) : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $account->credit > 0 ? number_format($account->credit, 2) : '-' }}
                            </td>
                        </tr>
                        @endforeach
                        
                        <tr class="table-info fw-bold">
                            <td colspan="3" class="text-center">Total</td>
                            <td class="text-end">₹{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end">₹{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                
                @if(abs($totalDebit - $totalCredit) < 0.01)
                <div class="alert alert-success text-center mt-3 mb-0">
                    <i class="fas fa-check-circle"></i> The Trial Balance is in balance! Total Debits match Total Credits.
                </div>
                @else
                <div class="alert alert-danger text-center mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Warning: Debits and Credits do not match. Difference: ₹{{ number_format(abs($totalDebit - $totalCredit), 2) }}
                </div>
                @endif
            </x-card>
        </div>
    </div>

</div>
@endsection
