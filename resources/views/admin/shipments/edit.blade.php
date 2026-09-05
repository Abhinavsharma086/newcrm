@extends('layouts.app')

@section('title', 'Edit Shipment')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.shipments.show', $shipment) }}">{{ $shipment->shipment_no }}</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Shipment #{{ $shipment->shipment_no }}</h2>
        <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <x-card>
        <div class="card-body">
            <form action="{{ route('admin.shipments.update', $shipment) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label"><strong>Invoice:</strong></label>
                    <p class="form-control-plaintext">
                        {{ $shipment->invoice->invoice_no }} - {{ $shipment->invoice->customer->name }}
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="courier_name" class="form-label">Courier Name</label>
                            <input type="text" class="form-control @error('courier_name') is-invalid @enderror" 
                                   id="courier_name" name="courier_name" value="{{ old('courier_name', $shipment->courier_name) }}" maxlength="100">
                            @error('courier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tracking_no" class="form-label">Tracking No</label>
                            <input type="text" class="form-control @error('tracking_no') is-invalid @enderror" 
                                   id="tracking_no" name="tracking_no" value="{{ old('tracking_no', $shipment->tracking_no) }}" maxlength="100">
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
                                   id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no', $shipment->vehicle_no) }}" maxlength="50">
                            @error('vehicle_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="dispatched" {{ old('status', $shipment->status) == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="transit" {{ old('status', $shipment->status) == 'transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="delivered" {{ old('status', $shipment->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><strong>Dispatch Date:</strong></label>
                            <p class="form-control-plaintext">{{ $shipment->dispatch_date->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_delivery" class="form-label">Expected Delivery</label>
                            <input type="date" class="form-control @error('expected_delivery') is-invalid @enderror" 
                                   id="expected_delivery" name="expected_delivery" 
                                   value="{{ old('expected_delivery', $shipment->expected_delivery?->format('Y-m-d')) }}">
                            @error('expected_delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes', $shipment->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Shipment
                    </button>
                </div>
            </form>
        </div>
    </x-card>

</div>
@endsection
