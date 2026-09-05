@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Profit & Loss</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Profit & Loss Statement</h2>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <x-card class="mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $fromDate }}">
                </div>
                <div class="col-md-5">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </x-card>

    <div class="row">
        <div class="col-md-6">
            <x-card>
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Income</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($income as $inc)
                            <tr>
                                <td>{{ $inc->account_name }}</td>
                                <td class="text-end">₹{{ number_format($inc->current_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No income accounts</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Income</td>
                                <td class="text-end">₹{{ number_format($summary['total_income'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="col-md-6">
            <x-card>
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Expenses</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->account_name }}</td>
                                <td class="text-end">₹{{ number_format($expense->current_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No expense accounts</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Expenses</td>
                                <td class="text-end">₹{{ number_format($summary['total_expenses'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <x-card class="mt-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h5 class="text-muted">Total Income</h5>
                    <h3 class="text-success">₹{{ number_format($summary['total_income'], 2) }}</h3>
                </div>
                <div class="col-md-4">
                    <h5 class="text-muted">Total Expenses</h5>
                    <h3 class="text-danger">₹{{ number_format($summary['total_expenses'], 2) }}</h3>
                </div>
                <div class="col-md-4">
                    <h5 class="text-muted">Net Profit/Loss</h5>
                    <h3 class="{{ $summary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        ₹{{ number_format($summary['net_profit'], 2) }}
                    </h3>
                </div>
            </div>
        </div>
    </x-card>

</div>
@endsection
