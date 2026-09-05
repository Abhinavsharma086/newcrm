@extends('layouts.app')

@section('title', 'Material Logs')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
<li class="breadcrumb-item active">Material {{ ucfirst($type) }} logs</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Material {{ ucfirst($type) }} Ledger (Excel-wise Logs)</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#singleEntryModal">
                <i class="fas fa-plus"></i> Add Single Entry
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkEntryModal">
                <i class="fas fa-file-import"></i> Bulk Import
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <!-- Toggle Inward vs Outward -->
    <div class="mb-4">
        <a href="{{ route('admin.inventory.logs', ['type' => 'inward']) }}" class="btn btn-{{ $type == 'inward' ? 'primary' : 'outline-primary' }}">
            <i class="fas fa-arrow-alt-circle-down"></i> Material Inwards
        </a>
        <a href="{{ route('admin.inventory.logs', ['type' => 'outward']) }}" class="btn btn-{{ $type == 'outward' ? 'danger' : 'outline-danger' }}">
            <i class="fas fa-arrow-alt-circle-up"></i> Material Outwards
        </a>
    </div>

    <x-card>
        <table id="materialLogsTable" class="table table-hover align-middle table-bordered small">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Material Code</th>
                    <th>Material Description</th>
                    <th>UOM</th>
                    <th>Qty</th>
                    <th>Unit Rate</th>
                    <th>Classification</th>
                    <th>Supplied / Consumed Against</th>
                    @if($type == 'inward')
                        <th>Supplier</th>
                        <th>Ordered</th>
                    @else
                        <th>Client</th>
                        <th>Consumed</th>
                        <th>Meter No.</th>
                    @endif
                    <th>WO / Invoice</th>
                    <th>Store / Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->log_date ? $log->log_date->format('d.m.Y') : '-' }}</td>
                    <td><code>{{ $log->material_code }}</code></td>
                    <td><strong>{{ $log->material_description }}</strong></td>
                    <td>{{ $log->uom }}</td>
                    <td>{{ number_format($log->qty, 2) }}</td>
                    <td>{{ $log->unit_rate ? '₹' . number_format($log->unit_rate, 2) : '-' }}</td>
                    <td>
                        <span class="badge bg-{{ str_contains(strtolower($log->material_type), 'free') ? 'warning text-dark' : 'info' }}">
                            {{ $log->material_type ?? 'Purchase' }}
                        </span>
                    </td>
                    <td>{{ $log->supplied_against ?? '-' }}</td>
                    @if($type == 'inward')
                        <td>{{ $log->supplier_name ?? ($log->supplier?->name ?? '-') }}</td>
                        <td>{{ $log->ordered_or_consumed ?? '-' }}</td>
                    @else
                        <td>{{ $log->client_name ?? ($log->client?->name ?? '-') }}</td>
                        <td>{{ $log->ordered_or_consumed ?? '-' }}</td>
                        <td><span class="font-monospace text-primary fw-bold">{{ $log->meter_no ?? '-' }}</span></td>
                    @endif
                    <td><code>{{ $log->wo_invoice ?? '-' }}</code></td>
                    <td>{{ $log->store_name ?? ($log->warehouse?->name ?? '-') }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editLogModal{{ $log->id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                
                <!-- Edit Modal -->
                <div class="modal fade text-dark" id="editLogModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('admin.inventory.logs.update', $log->id) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="log_type" value="{{ $type }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit {{ ucfirst($type) }} Log</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                            <input type="date" name="log_date" class="form-control" value="{{ $log->log_date ? $log->log_date->format('Y-m-d') : '' }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Select Material / Product <span class="text-danger">*</span></label>
                                            <select name="product_id" class="form-select" required>
                                                <option value="">-- Select Material --</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" {{ $log->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }} [{{ $p->material_code }}] (Stock: {{ $p->current_stock }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Qty <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="qty" class="form-control" value="{{ $log->qty }}" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Unit Rate (₹)</label>
                                            <input type="number" step="0.01" name="unit_rate" class="form-control" value="{{ $log->unit_rate }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Classification <span class="text-danger">*</span></label>
                                            <select name="material_type" class="form-select" required>
                                                <option value="Purchase" {{ $log->material_type == 'Purchase' ? 'selected' : '' }}>Purchase (MQ Supplied)</option>
                                                <option value="Free Issue Material" {{ $log->material_type == 'Free Issue Material' ? 'selected' : '' }}>Free Issue Material (Client Supplied)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Supplied/Consumed Against Info</label>
                                            <input type="text" name="supplied_against" class="form-control" value="{{ $log->supplied_against }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">WO / Invoice No.</label>
                                            <input type="text" name="wo_invoice" class="form-control" value="{{ $log->wo_invoice }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        @if($type == 'inward')
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Supplier</label>
                                                <select name="supplier_id" class="form-select">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" {{ $log->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Ordered</label>
                                                <input type="text" name="ordered_or_consumed" class="form-control" value="{{ $log->ordered_or_consumed }}">
                                            </div>
                                        @else
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Client</label>
                                                <select name="client_id" class="form-select">
                                                    <option value="">-- Select Client --</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->id }}" {{ $log->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Consumed Indicator</label>
                                                <input type="text" name="ordered_or_consumed" class="form-control" value="{{ $log->ordered_or_consumed }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">Meter No (For FIM Reconciliation)</label>
                                                <input type="text" name="meter_no" class="form-control" value="{{ $log->meter_no }}">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Store / Location Name</label>
                                            <input type="text" name="store_name" class="form-control" value="{{ $log->store_name }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Map to Store Master</label>
                                            <select name="warehouse_id" class="form-select">
                                                <option value="">-- Select Store --</option>
                                                @foreach($warehouses as $w)
                                                    <option value="{{ $w->id }}" {{ $log->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Ledger Entry</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </x-card>
</div>

<!-- Modal: Single Entry -->
<div class="modal fade text-dark" id="singleEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.inventory.logs.store') }}" method="POST">
                @csrf
                <input type="hidden" name="log_type" value="{{ $type }}">
                <div class="modal-header">
                    <h5 class="modal-title">Record Single {{ ucfirst($type) }} Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Select Material / Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">-- Select Material --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} [{{ $p->material_code }}] (Stock: {{ $p->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Qty <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="qty" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Unit Rate (₹)</label>
                            <input type="number" step="0.01" name="unit_rate" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Classification <span class="text-danger">*</span></label>
                            <select name="material_type" class="form-select" required>
                                <option value="Purchase">Purchase (MQ Supplied)</option>
                                <option value="Free Issue Material">Free Issue Material (Client Supplied)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Supplied/Consumed Against Info</label>
                            <input type="text" name="supplied_against" class="form-control" placeholder="e.g. Purchase Order, Site Delivery">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">WO / Invoice No.</label>
                            <input type="text" name="wo_invoice" class="form-control" placeholder="Work order or invoice code">
                        </div>
                    </div>

                    <div class="row">
                        @if($type == 'inward')
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ordered</label>
                                <input type="text" name="ordered_or_consumed" class="form-control" placeholder="e.g. Yes/No/Pending">
                            </div>
                        @else
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Consumed Indicator</label>
                                <input type="text" name="ordered_or_consumed" class="form-control" placeholder="e.g. Yes/No">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Meter No (For FIM Reconciliation)</label>
                                <input type="text" name="meter_no" class="form-control" placeholder="Reconcile against specific meter">
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Store / Location Name</label>
                            <input type="text" name="store_name" class="form-control" placeholder="Store description">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Map to Store Master</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">-- Select Store --</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Ledger Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Bulk Entry -->
<div class="modal fade text-dark" id="bulkEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.inventory.logs.bulk') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="log_type" value="{{ $type }}">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Import {{ ucfirst($type) }} Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Sheet <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                        <small class="text-muted d-block mt-2">Make sure Excel structure matches: Date, Material Code, Material Description, Rate, UOM, Qty, Type, Details, Vendor/Client, Consumed/Ordered, Invoice, Store.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Import Bulk Entries</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#materialLogsTable').DataTable({
        responsive: true
    });
});
</script>
@endpush
