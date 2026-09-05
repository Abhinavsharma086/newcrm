@extends('layouts.admin')

@section('title', 'Warehouse Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Warehouse Details</h5>
                <div>
                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Name</th>
                        <td>{{ $warehouse->name }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $warehouse->location }}</td>
                    </tr>
                    <tr>
                        <th>Manager</th>
                        <td>{{ $warehouse->manager->name ?? 'Not assigned' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $warehouse->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Stock Transactions</h5>
            </div>
            <div class="card-body">
                @if($warehouse->stockTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouse->stockTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $transaction->product->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type == 'in' ? 'success' : 'warning' }}">
                                            {{ strtoupper($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->quantity }} {{ strtoupper($transaction->product->unit) }}</td>
                                    <td>{{ $transaction->reference ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No stock transactions found.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Warehouse
                </a>
                <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="return confirm('Are you sure you want to delete this warehouse?')">
                        <i class="fas fa-trash"></i> Delete Warehouse
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
