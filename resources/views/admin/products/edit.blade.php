@extends('layouts.app')

@section('title', 'Edit Product / Service')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Inventory Items</a></li>
<li class="breadcrumb-item active">Edit Item</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Product or Service Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="sku" class="form-label">SKU / Item Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                           id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Item Name / Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Item Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="material" {{ old('type', $product->type) == 'material' ? 'selected' : '' }}>Material / Product</option>
                        <option value="service" {{ old('type', $product->type) == 'service' ? 'selected' : '' }}>Service / Installation Charge</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="description" class="form-label">Detailed Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    @if($product->image_path)
                        <div class="mb-2">
                            <img src="{{ asset($product->image_path) }}" alt="Current Image" style="max-height: 50px;">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                    <label class="form-check-label text-danger" for="remove_image" style="font-size: 0.85em;">
                                        <i class="fas fa-trash-alt"></i> Remove Image
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="material_code" class="form-label">Material Code (SAP)</label>
                    <input type="text" class="form-control @error('material_code') is-invalid @enderror" 
                           id="material_code" name="material_code" value="{{ old('material_code', $product->material_code) }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="hsn_code" class="form-label">HSN Code</label>
                    <input type="text" class="form-control @error('hsn_code') is-invalid @enderror" 
                           id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="inventory_type" class="form-label">Inventory Classification <span class="text-danger">*</span></label>
                    <select class="form-select @error('inventory_type') is-invalid @enderror" id="inventory_type" name="inventory_type" required>
                        <option value="purchase" {{ old('inventory_type', $product->inventory_type) == 'purchase' ? 'selected' : '' }}>MQ Supplied (Purchase)</option>
                        <option value="free_issue" {{ old('inventory_type', $product->inventory_type) == 'free_issue' ? 'selected' : '' }}>Free Issue Material (Client Supplied)</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="unit" class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                        <option value="M" {{ old('unit', $product->unit) == 'M' ? 'selected' : '' }}>Meter (M)</option>
                        <option value="Nos" {{ old('unit', $product->unit) == 'Nos' ? 'selected' : '' }}>Numbers (Nos)</option>
                        <option value="Set" {{ old('unit', $product->unit) == 'Set' ? 'selected' : '' }}>Set</option>
                        <option value="pcs" {{ old('unit', $product->unit) == 'pcs' ? 'selected' : '' }}>Pieces</option>
                        <option value="box" {{ old('unit', $product->unit) == 'box' ? 'selected' : '' }}>Box</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                           id="price" name="price" value="{{ old('price', $product->price) }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="tax_rate" class="form-label">GST Rate (%) <span class="text-danger">*</span></label>
                    <select class="form-select @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" required>
                        <option value="0" {{ old('tax_rate', $product->tax_rate) == '0' ? 'selected' : '' }}>0%</option>
                        <option value="5" {{ old('tax_rate', $product->tax_rate) == '5' ? 'selected' : '' }}>5%</option>
                        <option value="12" {{ old('tax_rate', $product->tax_rate) == '12' ? 'selected' : '' }}>12%</option>
                        <option value="18" {{ old('tax_rate', $product->tax_rate) == '18' ? 'selected' : '' }}>18%</option>
                        <option value="28" {{ old('tax_rate', $product->tax_rate) == '28' ? 'selected' : '' }}>28%</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="reorder_level" class="form-label">Reorder Level <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('reorder_level') is-invalid @enderror" 
                           id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="current_stock" class="form-label">Current Stock <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('current_stock') is-invalid @enderror" 
                           id="current_stock" name="current_stock" value="{{ old('current_stock', $product->current_stock) }}" required>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Item
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
