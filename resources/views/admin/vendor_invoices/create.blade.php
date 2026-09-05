@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Record Incoming Vendor Bill / Invoice</h5>
        </div>
        <div class="card-body">
            <!-- PO quick selection helper -->
            <form method="GET" class="row g-3 mb-4 border-bottom pb-3 align-items-end" id="poSelectForm">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Select Billing Pathway <span class="text-danger">*</span></label>
                    <select class="form-select" name="vendor_po_id" onchange="document.getElementById('poSelectForm').submit()">
                        <option value="">-- Direct Payment (Without PO e.g. Rent, Electricity) --</option>
                        @foreach($pos as $p)
                        <option value="{{ $p->id }}" {{ isset($selectedPo) && $selectedPo->id == $p->id ? 'selected' : '' }}>
                            Match Against Purchase Order: {{ $p->po_number }} (Value: ₹{{ number_format($p->po_value, 2) }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    @if(isset($selectedPo))
                        <span class="badge bg-success p-2 mb-2"><i class="fas fa-check me-1"></i>3-Way Check Enabled</span>
                        <div class="text-muted small">Systems will run automatic variance validation checks against the items ordered in PO #{{ $selectedPo->po_number }}.</div>
                    @else
                        <span class="badge bg-warning text-dark p-2 mb-2"><i class="fas fa-exclamation-circle me-1"></i>Direct Expense (Bypassing 3-Way Match)</span>
                        <div class="text-muted small">No PO mapping. System will directly record the invoice and pass it straight to the payment stage.</div>
                    @endif
                </div>
            </form>

            <form action="{{ route('admin.vendor-invoices.store') }}" method="POST">
                @csrf
                @if(isset($selectedPo))
                <input type="hidden" name="vendor_po_id" value="{{ $selectedPo->id }}">
                <input type="hidden" name="vendor_id" value="{{ $selectedPo->vendor_id }}">
                @endif

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        @if(isset($selectedPo))
                        <input type="text" class="form-control bg-light" value="{{ $selectedPo->vendor->name }}" disabled>
                        @else
                        <select class="form-select" name="vendor_id" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vendor Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="invoice_number" required placeholder="e.g. INV-90082">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Taxable Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="invoice_amount" id="taxableAmt" required value="{{ isset($selectedPo) ? $selectedPo->items->sum(fn($i) => $i->qty * $i->rate) : '0.00' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GST Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="gst_amount" id="gstAmt" required value="{{ isset($selectedPo) ? $selectedPo->items->sum(fn($i) => ($i->qty * $i->rate) * ($i->gst_percent / 100)) : '0.00' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">TDS Deducted (₹)</label>
                        <input type="number" step="0.01" class="form-control" name="tds_amount" id="tdsAmt" value="{{ isset($selectedPo) ? ($selectedPo->items->sum(fn($i) => $i->qty * $i->rate) * ($selectedPo->tds_percent / 100)) : '0.00' }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Audit / Matching Notes</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Record matching differences, mismatch flags if any."></textarea>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <a href="{{ route('admin.vendor-invoices.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Bill & Run Matcher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
