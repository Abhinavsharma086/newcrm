@extends('layouts.app')

@section('title', 'Customer Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Customer Report</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Customer Report</h2>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <x-card>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover dataTable">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-center">Total Invoices</th>
                            <th class="text-end">Total Spent</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        @php
                            $totalSpent = $customer->invoices->sum('total') ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->company_name ?? 'N/A' }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td class="text-center">{{ $customer->invoices_count }}</td>
                            <td class="text-end">₹{{ number_format($totalSpent, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $customer->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>

</div>
@endsection
