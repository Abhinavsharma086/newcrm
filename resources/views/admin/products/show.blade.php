@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Product Details</h5>
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">SKU</th>
                        <td>{{ $product->sku }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $product->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $product->category ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Unit</th>
                        <td>{{ strtoupper($product->unit) }}</td>
                    </tr>
                    <tr>
                        <th>HSN Code</th>
                        <td>{{ $product->hsn_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Tax Rate</th>
                        <td>{{ $product->tax_rate }}%</td>
                    </tr>
                    <tr>
                        <th>Current Stock</th>
                        <td>
                            <span class="badge bg-{{ $product->current_stock <= $product->reorder_level ? 'danger' : 'success' }}">
                                {{ $product->current_stock }} {{ strtoupper($product->unit) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Reorder Level</th>
                        <td>{{ $product->reorder_level }} {{ strtoupper($product->unit) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Stock Transactions</h5>
            </div>
            <div class="card-body">
                @if($product->stockTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Warehouse</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stockTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type == 'in' ? 'success' : 'warning' }}">
                                            {{ strtoupper($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->quantity }} {{ strtoupper($product->unit) }}</td>
                                    <td>{{ $transaction->warehouse->name ?? 'N/A' }}</td>
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
                <a href="{{ route('admin.inventory.index', ['product' => $product->id]) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-warehouse"></i> View Inventory
                </a>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="return confirm('Are you sure you want to delete this product?')">
                        <i class="fas fa-trash"></i> Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
