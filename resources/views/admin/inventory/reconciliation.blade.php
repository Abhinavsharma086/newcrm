@extends('layouts.app')

@section('title', 'FIM Reconciliation')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
<li class="breadcrumb-item active">Free Issue Reconciliation</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-balance-scale text-primary me-2"></i>Free Issue Material Reconciliation</h2>
        <p class="text-muted">Reconciliation list of client supplied Free Issue Materials tracked per unique customer Meter Number.</p>
    </div>

    @forelse($grouped as $meterNo => $logs)
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                Meter Number: <span class="badge bg-dark font-monospace fs-6">{{ $meterNo ?: 'Unassigned / General Outward' }}</span>
            </h5>
            @php
                $customer = \App\Models\Customer::where('meter_no', $meterNo)->first();
            @endphp
            @if($customer)
                <span class="small text-muted">
                    Customer: <strong><a href="{{ route('admin.customers.show', $customer) }}" target="_blank">{{ $customer->name }}</a></strong> ({{ $customer->phone }})
                </span>
            @else
                <span class="small text-danger">Customer not resolved for this meter</span>
            @endif
        </div>
        <div class="card-body py-2">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Material Code</th>
                        <th>Material Description</th>
                        <th>UOM</th>
                        <th class="text-end">Qty Consumed</th>
                        <th>WO / Invoice No.</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->log_date ? $log->log_date->format('d.m.Y') : '-' }}</td>
                        <td><code>{{ $log->material_code }}</code></td>
                        <td><strong>{{ $log->material_description }}</strong></td>
                        <td>{{ $log->uom }}</td>
                        <td class="text-end fw-bold">{{ number_format($log->qty, 2) }}</td>
                        <td><code>{{ $log->wo_invoice ?? '-' }}</code></td>
                        <td>
                            <span class="badge bg-success">Reconciled</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <x-card>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-balance-scale fa-3x mb-3"></i>
            <p>No outward Free Issue Material logs recorded to perform reconciliation.</p>
        </div>
    </x-card>
    @endforelse
</div>
@endsection
