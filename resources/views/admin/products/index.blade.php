@extends('layouts.app')

@section('title', 'Products & Services')

@section('breadcrumb')
<li class="breadcrumb-item active">Products & Services</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Products & Services Management</h2>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product / Service
        </a>
    </div>
    
    <x-card>
        <table id="productsTable" class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Classification</th>
                    <th>Category</th>
                    <th>HSN Code</th>
                    <th>Price</th>
                    <th>GST</th>
                    <th>Stock (UOM)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        @if($product->image_path)
                            <img src="{{ asset($product->image_path) }}" alt="Image" style="max-width: 40px; max-height: 40px; object-fit: cover; border-radius: 4px;">
                        @else
                            <div style="width: 40px; height: 40px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6;">
                                <i class="fas fa-image text-muted" style="font-size: 14px;"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if($product->material_code)
                            <br><small class="text-muted">SAP Code: {{ $product->material_code }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $product->type == 'material' ? 'info' : 'secondary' }}">
                            {{ ucfirst($product->type) }}
                        </span>
                        <br>
                        <small class="text-muted">
                            {{ $product->inventory_type == 'purchase' ? 'MQ Purchased' : 'Free Issue' }}
                        </small>
                    </td>
                    <td>{{ $product->category }}</td>
                    <td>{{ $product->hsn_code ?? '-' }}</td>
                    <td>₹{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->tax_rate }}%</td>
                    <td>
                        @if($product->type == 'material')
                            <span class="badge bg-{{ $product->current_stock <= $product->reorder_level ? 'danger' : 'success' }}">
                                {{ $product->current_stock }} {{ $product->unit }}
                            </span>
                        @else
                            <span class="text-muted">N/A (Service)</span>
                        @endif
                    </td>
                    <td>
                        @if($product->type == 'material')
                            @if($product->current_stock <= $product->reorder_level)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        @else
                            <span class="badge bg-light text-dark">Service Item</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info text-white">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
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
    $('#productsTable').DataTable({
        responsive: true
    });
});

function deleteProduct(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the product/service!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ED1C24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/products/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
