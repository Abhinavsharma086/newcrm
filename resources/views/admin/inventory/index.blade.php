@extends('layouts.app')
@section('title', 'Inventory')
@section('breadcrumb')
<li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Management</h2>
        <div>
            <a href="{{ route('admin.inventory.transactions') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-list"></i> Transactions
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-boxes" title="Total Products" :value="$products->count()" color="navy"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-rupee-sign" title="Stock Value" :value="'₹' . number_format($totalStockValue, 2)" color="success"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-exclamation-triangle" title="Low Stock" :value="$lowStockProducts->count()" color="warning"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-warehouse" title="Warehouses" :value="$warehouses->count()" color="red"/>
        </div>
    </div>

    {{-- Stock In / Out Forms --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <x-card title="Stock In">
                <form action="{{ route('admin.inventory.stock-in') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="PO / GRN number">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Add Stock In
                            </button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
        <div class="col-md-6">
            <x-card title="Stock Out">
                <form action="{{ route('admin.inventory.stock-out') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Invoice / SO number">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-minus-circle"></i> Add Stock Out
                            </button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Products Table --}}
    <x-card title="Product Stock Levels">
        <table id="inventoryTable" class="table table-hover">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Unit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="{{ $product->current_stock <= $product->reorder_level ? 'table-warning' : '' }}">
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category }}</td>
                    <td><strong>{{ $product->current_stock }}</strong></td>
                    <td>{{ $product->reorder_level }}</td>
                    <td>{{ $product->unit }}</td>
                    <td>
                        @if($product->current_stock == 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($product->current_stock <= $product->reorder_level)
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
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
    $('#inventoryTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'pdf', 'print'] });
});
</script>
@endpush
