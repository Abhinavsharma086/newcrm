@extends('layouts.app')

@section('title', 'Inventory Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Inventory Report</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Report</h2>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Products</h6>
                    <h4>{{ $summary['total_products'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Stock Value</h6>
                    <h4>₹{{ number_format($summary['total_stock_value'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6>Low Stock Items</h6>
                    <h4>{{ $summary['low_stock_items'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Out of Stock</h6>
                    <h4>{{ $summary['out_of_stock'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if($lowStockProducts->count() > 0)
    <x-card class="mb-3">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">⚠️ Low Stock Alert</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Min Stock Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end text-danger">{{ $product->current_stock }}</td>
                            <td class="text-end">{{ $product->min_stock_level }}</td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary">
                                    Restock
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>
    @endif

    <x-card>
        <div class="card-header">
            <h5 class="mb-0">All Products</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover dataTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Min Level</th>
                            <th class="text-end">Stock Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category ?? 'N/A' }}</td>
                            <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                            <td class="text-end">{{ $product->current_stock }}</td>
                            <td class="text-end">{{ $product->min_stock_level }}</td>
                            <td class="text-end">₹{{ number_format($product->current_stock * $product->price, 2) }}</td>
                            <td>
                                @if($product->current_stock == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($product->current_stock <= $product->min_stock_level)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
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
