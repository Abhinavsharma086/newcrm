@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- PO details card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Client PO Details: #{{ $client_po->po_number }}</h5>
                    <span class="badge bg-success">{{ strtoupper($client_po->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">CLIENT</div>
                            <div class="fw-bold">{{ $client_po->client->name }}</div>
                            <div>{{ $client_po->client->address ?? '' }}</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-muted small">PO DATE</div>
                            <div class="fw-bold">{{ $client_po->po_date->format('d M Y') }}</div>
                            <div class="text-muted small mt-2">RETENTION PERCENT</div>
                            <div class="fw-bold">{{ $client_po->retention_percent }}%</div>
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3">Service Line Items Tracking</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Description</th>
                                    <th>HSN/SAC</th>
                                    <th>Ordered Qty</th>
                                    <th>Executed Qty</th>
                                    <th>Remaining Qty</th>
                                    <th>Unit</th>
                                    <th>Rate (₹)</th>
                                    <th>Value (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client_po->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->hsn_code ?? '-' }}</td>
                                    <td class="text-end">{{ $item->qty }}</td>
                                    <td class="text-end text-success fw-bold">{{ $item->executed_qty }}</td>
                                    <td class="text-end text-warning">{{ $item->remaining_qty }}</td>
                                    <td class="text-center">{{ $item->unit ?? '-' }}</td>
                                    <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item->total_value, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Daily progress logging logs -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-1"></i> Progress Entry Logs / Measurement Sheet</h5>
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate ?? '' }}" placeholder="Start Date">
                        <span class="text-white">to</span>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate ?? '' }}" placeholder="End Date">
                        <button type="submit" class="btn btn-sm btn-light">Filter</button>
                        @if(isset($startDate) || isset($endDate))
                            <a href="{{ route('admin.client-pos.show', $client_po) }}" class="btn btn-sm btn-outline-light text-white border-0">Clear</a>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Item Description</th>
                                <th class="text-end">Qty Executed</th>
                                <th>Logged By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($progressEntries as $entry)
                            <tr>
                                <td>{{ $entry->entry_date->format('d M Y') }}</td>
                                <td>{{ $entry->item->description }}</td>
                                <td class="text-end fw-bold text-success">{{ $entry->executed_qty }}</td>
                                <td>{{ $entry->user->name }}</td>
                                <td>{{ $entry->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No progress entries match the filters. Use the right panel to record work execution.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Progress entry submission sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-hammer me-1"></i> Log Daily Execution</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.client-pos.progress', $client_po) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select PO Line Item <span class="text-danger">*</span></label>
                            <select class="form-select" name="client_po_item_id" required>
                                <option value="">Select PO Item</option>
                                @foreach($client_po->items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->description }} (Limit: {{ $item->remaining_qty }} left)
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Execution Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="entry_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Qty Executed <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="executed_qty" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Execution Notes / Remarks</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Describe the physical status, site info etc."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-1"></i>Submit Progress Entry</button>
                    </form>
                </div>
            </div>

            <!-- Create Invoice from execution progress link -->
            <div class="card shadow-sm border-0 border-primary text-center p-3 bg-white">
                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                <h6>Raise RA Bill / Invoice</h6>
                <p class="text-muted small">Quickly generate a GST Invoice pre-filled with cumulative executed quantities.</p>
                <a href="{{ route('admin.invoices.create') }}?client_po_id={{ $client_po->id }}" class="btn btn-primary btn-sm">Generate Invoice</a>
            </div>
        </div>
    </div>
</div>
@endsection
