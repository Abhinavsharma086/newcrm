@extends('layouts.app')

@section('title', 'Create Shipment')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Shipment</h2>
        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <x-card>
        <div class="card-body">
                    <form action="{{ route('admin.shipments.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invoice_id" class="form-label">Invoice <span class="text-danger">*</span></label>
                                    <select class="form-control @error('invoice_id') is-invalid @enderror" 
                                            id="invoice_id" name="invoice_id" required>
                                        <option value="">Select Invoice</option>
                                        @foreach($invoices as $invoice)
                                            <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                                {{ $invoice->invoice_no }} - {{ $invoice->customer->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('invoice_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dispatch_date" class="form-label">Dispatch Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('dispatch_date') is-invalid @enderror" 
                                           id="dispatch_date" name="dispatch_date" value="{{ old('dispatch_date', date('Y-m-d')) }}" required>
                                    @error('dispatch_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="courier_name" class="form-label">Courier Name</label>
                                    <input type="text" class="form-control @error('courier_name') is-invalid @enderror" 
                                           id="courier_name" name="courier_name" value="{{ old('courier_name') }}" maxlength="100">
                                    @error('courier_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tracking_no" class="form-label">Tracking No</label>
                                    <input type="text" class="form-control @error('tracking_no') is-invalid @enderror" 
                                           id="tracking_no" name="tracking_no" value="{{ old('tracking_no') }}" maxlength="100">
                                    @error('tracking_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_no" class="form-label">Vehicle No</label>
                                    <input type="text" class="form-control @error('vehicle_no') is-invalid @enderror" 
                                           id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no') }}" maxlength="50">
                                    @error('vehicle_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expected_delivery" class="form-label">Expected Delivery</label>
                                    <input type="date" class="form-control @error('expected_delivery') is-invalid @enderror" 
                                           id="expected_delivery" name="expected_delivery" value="{{ old('expected_delivery') }}">
                                    @error('expected_delivery')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Shipment
                            </button>
                        </div>
                    </form>
        </div>
    </x-card>

</div>
@endsection
