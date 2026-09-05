@extends('layouts.app')

@section('title', 'Stock Transactions')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
<li class="breadcrumb-item active">Stock Transactions</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Stock Transactions Log</h2>
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card>
        <table id="transactionsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Ref No</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('d M Y H:i') }}</td>
                    <td>{{ $t->product->name }}</td>
                    <td>{{ $t->warehouse->name }}</td>
                    <td>
                        <span class="badge bg-{{ $t->type == 'in' ? 'success' : 'danger' }}">
                            {{ strtoupper($t->type) }}
                        </span>
                    </td>
                    <td>{{ $t->quantity }}</td>
                    <td><code>{{ $t->reference_no ?? 'N/A' }}</code></td>
                    <td>{{ $t->creator->name }}</td>
                    <td>
                        @if($t->approved_at)
                            <span class="badge bg-success">Approved</span>
                            <small class="d-block text-muted">by {{ $t->approver->name }}</small>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(!$t->approved_at)
                                <form action="{{ route('admin.inventory.approve-transaction', $t) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.inventory.delete-transaction', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction log?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        order: [[0, 'desc']]
    });
});
</script>
@endpush
