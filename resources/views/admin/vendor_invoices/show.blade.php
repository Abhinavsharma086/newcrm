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
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor Bill: #{{ $vendor_invoice->invoice_number }}</h5>
                    <div>
                        @if($vendor_invoice->approval_status !== 'approved')
                        <form action="{{ route('admin.vendor-invoices.approve', $vendor_invoice) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Approve Bill</button>
                        </form>
                        @else
                        <span class="badge bg-success">APPROVED BY ACCOUNTS</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Matching and ITC Alerts -->
                    @if($vendor_invoice->matching_status === 'flagged')
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i><strong>3-Way Matching Variance Flagged!</strong> The invoice net amount exceeds the associated purchase order limit (including the 2% tolerance threshold). Manual audit required.
                    </div>
                    @else
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle me-2"></i><strong>3-Way Match Passed:</strong> Quantities and rates match PO contract constraints.
                    </div>
                    @endif

                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">VENDOR (SUPPLIER)</div>
                            <div class="fw-bold fs-5 text-primary">{{ $vendor_invoice->vendor->name }}</div>
                            <div>{{ $vendor_invoice->vendor->address ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-muted small">BILLING DATE</div>
                            <div class="fw-bold">{{ $vendor_invoice->invoice_date->format('d M Y') }}</div>
                            <div class="text-muted small mt-2">ASSOCIATED VENDOR PO</div>
                            <div class="fw-bold">{{ $vendor_invoice->vendorPo ? $vendor_invoice->vendorPo->po_number : 'Direct (No PO)' }}</div>
                        </div>
                    </div>

                    <h5 class="mb-3">Billing Amount Breakup</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Taxable Value</th>
                                    <th>GST Amount</th>
                                    <th>TDS Deducted</th>
                                    <th class="table-primary text-center">Net Payable Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>₹{{ number_format($vendor_invoice->invoice_amount, 2) }}</td>
                                    <td>₹{{ number_format($vendor_invoice->gst_amount, 2) }}</td>
                                    <td class="text-danger">- ₹{{ number_format($vendor_invoice->tds_amount, 2) }}</td>
                                    <td class="table-primary text-center fw-bold fs-5 text-primary">₹{{ number_format($vendor_invoice->net_payable, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($vendor_invoice->notes)
                    <div class="alert alert-info">
                        <strong>Remarks / Audit Trail Notes:</strong><br>
                        {{ $vendor_invoice->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment logging panel -->
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Record Payment Voucher</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Net Payable:</td><td class="text-end fw-bold">₹{{ number_format($vendor_invoice->net_payable, 2) }}</td></tr>
                        <tr><td>Paid:</td><td class="text-end text-success fw-bold">₹{{ number_format($vendor_invoice->paid_amount, 2) }}</td></tr>
                        <tr><td>Balance Due:</td><td class="text-end text-danger fw-bold fs-5">₹{{ number_format($vendor_invoice->balance_due, 2) }}</td></tr>
                    </table>

                    @if($vendor_invoice->balance_due > 0)
                    <form action="{{ route('admin.vendor-invoices.record-payment', $vendor_invoice) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Amount (₹)</label>
                            <input type="number" step="0.01" class="form-control" name="amount" value="{{ $vendor_invoice->balance_due }}" max="{{ $vendor_invoice->balance_due }}" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-money-bill-wave me-1"></i>Record Bank Payment</button>
                    </form>
                    @else
                    <div class="alert alert-success text-center mt-3 mb-0">
                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                        <strong>Paid In Full</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- GST Input Tax Credit (ITC) eligibility status card -->
            <div class="card shadow-sm border-0 text-center p-3 bg-white">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-percentage fa-2x text-success me-2"></i>
                    <h6 class="mb-0">ITC Validation Status</h6>
                </div>
                @if($vendor_invoice->itc_eligible)
                <span class="badge bg-success py-2"><i class="fas fa-check me-1"></i>ITC CLAIM ELIGIBLE</span>
                @else
                <span class="badge bg-danger py-2"><i class="fas fa-times me-1"></i>ITC NOT CLAIMABLE</span>
                @endif
                <p class="text-muted small mt-2 mb-0">ITC validation compares vendor GSTIN state records vs purchase receipts.</p>
            </div>
        </div>
    </div>
</div>
@endsection
