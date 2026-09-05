@extends('layouts.app')
@section('title', 'Shipments')
@section('breadcrumb')
<li class="breadcrumb-item active">Shipments</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shipment Tracking</h2>
        <a href="{{ route('admin.shipments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Shipment
        </a>
    </div>

    <x-card>
        <table id="shipmentsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Shipment #</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Courier</th>
                    <th>Tracking No</th>
                    <th>Dispatch Date</th>
                    <th>Expected Delivery</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipments as $shipment)
                <tr>
                    <td>{{ $shipment->shipment_no }}</td>
                    <td>{{ $shipment->invoice->invoice_no }}</td>
                    <td>{{ $shipment->invoice->customer->name }}</td>
                    <td>{{ $shipment->courier_name ?? '—' }}</td>
                    <td>{{ $shipment->tracking_no ?? '—' }}</td>
                    <td>{{ $shipment->dispatch_date->format('d M Y') }}</td>
                    <td>{{ $shipment->expected_delivery ? $shipment->expected_delivery->format('d M Y') : '—' }}</td>
                    <td>
                        @php
                            $statusColor = ['dispatched' => 'primary', 'transit' => 'warning', 'delivered' => 'success'];
                        @endphp
                        <span class="badge bg-{{ $statusColor[$shipment->status] }}">
                            {{ ucfirst($shipment->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.shipments.edit', $shipment) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
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
    $('#shipmentsTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'pdf', 'print'] });
});
</script>
@endpush
