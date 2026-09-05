@extends('layouts.app')

@section('title', 'Shipment Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shipments.index') }}">Shipments</a></li>
<li class="breadcrumb-item active">{{ $shipment->shipment_no }}</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shipment #{{ $shipment->shipment_no }}</h2>
        <div>
            <a href="{{ route('admin.shipments.edit', $shipment) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <x-card>
                <div class="card-body">
                    <h5 class="mb-3">Shipment Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Shipment No:</strong> {{ $shipment->shipment_no }}
                            </p>
                            <p class="mb-2">
                                <strong>Invoice No:</strong> 
                                <a href="{{ route('admin.invoices.show', $shipment->invoice) }}">
                                    {{ $shipment->invoice->invoice_no }}
                                </a>
                            </p>
                            <p class="mb-2">
                                <strong>Customer:</strong> {{ $shipment->invoice->customer->name }}
                            </p>
                            <p class="mb-2">
                                <strong>Status:</strong>
                                @php
                                    $statusColor = ['dispatched' => 'primary', 'transit' => 'warning', 'delivered' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $statusColor[$shipment->status] }}">
                                    {{ ucfirst($shipment->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Courier Name:</strong> {{ $shipment->courier_name ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                <strong>Tracking No:</strong> {{ $shipment->tracking_no ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                <strong>Vehicle No:</strong> {{ $shipment->vehicle_no ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Dispatch Date:</strong> 
                                {{ $shipment->dispatch_date->format('d M Y') }}
                            </p>
                            <p class="mb-2">
                                <strong>Expected Delivery:</strong> 
                                {{ $shipment->expected_delivery ? $shipment->expected_delivery->format('d M Y') : 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if($shipment->delivered_at)
                            <p class="mb-2">
                                <strong>Delivered At:</strong> 
                                {{ $shipment->delivered_at->format('d M Y H:i') }}
                            </p>
                            @endif
                            <p class="mb-2">
                                <strong>Created By:</strong> 
                                {{ $shipment->creator->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    @if($shipment->notes)
                    <hr>
                    <div class="mb-3">
                        <strong>Notes:</strong>
                        <p class="text-muted mt-2">{{ $shipment->notes }}</p>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>

        <div class="col-md-4">
            <x-card>
                <div class="card-body">
                    <h5 class="mb-3">Customer Details</h5>
                    <p class="mb-2">
                        <strong>Name:</strong> {{ $shipment->invoice->customer->name }}
                    </p>
                    @if($shipment->invoice->customer->company_name)
                    <p class="mb-2">
                        <strong>Company:</strong> {{ $shipment->invoice->customer->company_name }}
                    </p>
                    @endif
                    <p class="mb-2">
                        <strong>Email:</strong> {{ $shipment->invoice->customer->email }}
                    </p>
                    <p class="mb-2">
                        <strong>Phone:</strong> {{ $shipment->invoice->customer->phone }}
                    </p>
                    @if($shipment->invoice->customer->address)
                    <p class="mb-2">
                        <strong>Address:</strong><br>
                        <span class="text-muted">{{ $shipment->invoice->customer->address }}</span>
                    </p>
                    @endif
                </div>
            </x-card>

            <x-card class="mt-3">
                <div class="card-body">
                    <h5 class="mb-3">Invoice Items</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shipment->invoice->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

</div>
@endsection
