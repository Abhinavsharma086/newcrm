@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Balance Sheet</h2>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-card>
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Assets</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <tr>
                                <td>{{ $asset->account_name }}</td>
                                <td class="text-end">₹{{ number_format($asset->current_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No asset accounts</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Assets</td>
                                <td class="text-end">₹{{ number_format($summary['total_assets'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="col-md-6">
            <x-card>
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Liabilities</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liabilities as $liability)
                            <tr>
                                <td>{{ $liability->account_name }}</td>
                                <td class="text-end">₹{{ number_format($liability->current_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No liability accounts</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Liabilities</td>
                                <td class="text-end">₹{{ number_format($summary['total_liabilities'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <x-card class="mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Equity</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equity as $eq)
                            <tr>
                                <td>{{ $eq->account_name }}</td>
                                <td class="text-end">₹{{ number_format($eq->current_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No equity accounts</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Equity</td>
                                <td class="text-end">₹{{ number_format($summary['total_equity'], 2) }}</td>
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
                    <h5 class="text-muted">Total Assets</h5>
                    <h3 class="text-primary">₹{{ number_format($summary['total_assets'], 2) }}</h3>
                </div>
                <div class="col-md-4">
                    <h5 class="text-muted">Total Liabilities</h5>
                    <h3 class="text-danger">₹{{ number_format($summary['total_liabilities'], 2) }}</h3>
                </div>
                <div class="col-md-4">
                    <h5 class="text-muted">Total Equity</h5>
                    <h3 class="text-success">₹{{ number_format($summary['total_equity'], 2) }}</h3>
                </div>
            </div>
        </div>
    </x-card>

</div>
@endsection
