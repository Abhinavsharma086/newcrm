@extends('layouts.admin')

@section('title', 'Quotations Dashboard')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Quotations Dashboard</h2>
            <p class="text-muted mb-0 small">Overview, pipeline analytics, and quotation lifecycle management</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-invoice-dollar me-1"></i> View GST Bills
            </a>
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Create Quotation
            </a>
        </div>
    </div>

    @php
        $today = now()->startOfDay();
        $in7Days = now()->addDays(7)->endOfDay();

        $totalCount = $quotations->count();
        $totalValue = $quotations->sum('total');
        $acceptedCount = $quotations->where('status', 'accepted')->count();
        $acceptedValue = $quotations->where('status', 'accepted')->sum('total');
        $pendingQuotations = $quotations->whereIn('status', ['draft', 'sent']);
        $pendingCount = $pendingQuotations->count();
        $pendingValue = $pendingQuotations->sum('total');
        $rejectedCount = $quotations->where('status', 'rejected')->count();
        $convRate = $totalCount > 0 ? round(($acceptedCount / $totalCount) * 100, 1) : 0;

        // Validity Tracking
        $expiredQuotes = $quotations->filter(fn($q) => $q->status !== 'accepted' && $q->status !== 'rejected' && $q->valid_till < $today);
        $expiringTodayQuotes = $quotations->filter(fn($q) => $q->status !== 'accepted' && $q->status !== 'rejected' && $q->valid_till->isSameDay($today));
        $expiringSoonQuotes = $quotations->filter(fn($q) => $q->status !== 'accepted' && $q->status !== 'rejected' && $q->valid_till > $today && $q->valid_till <= $in7Days);
        $validActiveQuotes = $quotations->filter(fn($q) => $q->status !== 'accepted' && $q->status !== 'rejected' && $q->valid_till > $in7Days);
    @endphp

    <!-- Validity Tracking & Reminder Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-bell text-warning fa-lg"></i>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Quotation Validity & Expiry Reminder Tracker</h6>
                    <small class="text-muted">Track expiration dates & follow up with clients before validity expires</small>
                </div>
            </div>
            <div class="text-muted small">
                Today: <strong class="text-dark">{{ now()->format('d M Y') }}</strong>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <!-- Expired -->
                <div class="col-xl-3 col-sm-6">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: #fff5f5; border-color: #fed7d7 !important; cursor: pointer;" onclick="filterValidity('Expired')">
                        <div>
                            <div class="text-danger fw-bold small text-uppercase" style="font-size: 0.72rem;"><i class="fas fa-times-circle me-1"></i> Already Expired</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ $expiredQuotes->count() }} <span class="fs-6 fw-normal text-muted">quotes</span></div>
                            <div class="text-muted small">Value: ₹{{ number_format($expiredQuotes->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-danger text-white">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>

                <!-- Expiring Today -->
                <div class="col-xl-3 col-sm-6">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: #fffaf0; border-color: #feebc8 !important; cursor: pointer;" onclick="filterValidity('Expiring Today')">
                        <div>
                            <div class="text-warning-emphasis fw-bold small text-uppercase" style="font-size: 0.72rem; color: #c05621 !important;"><i class="fas fa-bell me-1"></i> Expiring Today</div>
                            <div class="fs-4 fw-bold mt-1" style="color: #c05621;">{{ $expiringTodayQuotes->count() }} <span class="fs-6 fw-normal text-muted">quotes</span></div>
                            <div class="text-muted small">Value: ₹{{ number_format($expiringTodayQuotes->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-circle p-2 text-white" style="background: #dd6b20;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon (7 Days) -->
                <div class="col-xl-3 col-sm-6">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: #fffff0; border-color: #fefcbf !important; cursor: pointer;" onclick="filterValidity('Expiring Soon')">
                        <div>
                            <div class="fw-bold small text-uppercase" style="font-size: 0.72rem; color: #b7791f;"><i class="fas fa-hourglass-half me-1"></i> Expiring Soon (7 Days)</div>
                            <div class="fs-4 fw-bold mt-1" style="color: #b7791f;">{{ $expiringSoonQuotes->count() }} <span class="fs-6 fw-normal text-muted">quotes</span></div>
                            <div class="text-muted small">Value: ₹{{ number_format($expiringSoonQuotes->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-circle p-2 text-white" style="background: #d69e2e;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Active / Healthy -->
                <div class="col-xl-3 col-sm-6">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: #f0fff4; border-color: #c6f6d5 !important; cursor: pointer;" onclick="filterValidity('Valid')">
                        <div>
                            <div class="text-success fw-bold small text-uppercase" style="font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i> Active & Valid</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ $validActiveQuotes->count() }} <span class="fs-6 fw-normal text-muted">quotes</span></div>
                            <div class="text-muted small">Value: ₹{{ number_format($validActiveQuotes->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-success text-white">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Quotations</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $totalCount }}</div>
                            <div class="text-muted small mt-1">Value: <span class="fw-semibold text-primary">₹{{ number_format($totalValue, 2) }}</span></div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Converted / Accepted</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $acceptedCount }}</div>
                            <div class="text-muted small mt-1">Billed: <span class="fw-semibold text-success">₹{{ number_format($acceptedValue, 2) }}</span></div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Pending Pipeline</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $pendingCount }}</div>
                            <div class="text-muted small mt-1">Open: <span class="fw-semibold text-warning">₹{{ number_format($pendingValue, 2) }}</span></div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #fffbeb; color: #d97706;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Conversion Rate</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $convRate }}%</div>
                            <div class="text-muted small mt-1">{{ $acceptedCount }} of {{ $totalCount }} converted</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #faf5ff; color: #7c3aed;">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotations Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list text-primary"></i>
                <h5 class="mb-0 fw-bold text-dark">Quotation Records</h5>
            </div>
            <div class="d-flex gap-1 flex-wrap" id="statusFilters">
                <button type="button" class="btn btn-sm btn-outline-primary active filter-btn rounded-pill px-3" data-status="">All ({{ $totalCount }})</button>
                <button type="button" class="btn btn-sm btn-outline-danger filter-validity-btn rounded-pill px-3" data-validity="Expired">Expired ({{ $expiredQuotes->count() }})</button>
                <button type="button" class="btn btn-sm btn-outline-warning filter-validity-btn rounded-pill px-3" data-validity="Expiring">Expiring Soon ({{ $expiringTodayQuotes->count() + $expiringSoonQuotes->count() }})</button>
                <button type="button" class="btn btn-sm btn-outline-success filter-btn rounded-pill px-3" data-status="Accepted">Accepted ({{ $acceptedCount }})</button>
                <button type="button" class="btn btn-sm btn-outline-info filter-btn rounded-pill px-3" data-status="Sent">Sent ({{ $quotations->where('status', 'sent')->count() }})</button>
                <button type="button" class="btn btn-sm btn-outline-secondary filter-btn rounded-pill px-3" data-status="Draft">Draft ({{ $quotations->where('status', 'draft')->count() }})</button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($quotations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="quotationsTable">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr class="text-secondary small fw-bold">
                            <th class="ps-4 py-3">Quotation #</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Valid Till & Status</th>
                            <th class="text-end py-3">Total Amount</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-center py-3">GST Bill</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotations as $quotation)
                        @php
                            $isFinal = in_array($quotation->status, ['accepted', 'rejected']);
                            $daysDiff = $today->diffInDays($quotation->valid_till, false);
                            
                            $validityStatus = 'Valid';
                            if (!$isFinal) {
                                if ($quotation->valid_till < $today) {
                                    $validityStatus = 'Expired';
                                } elseif ($quotation->valid_till->isSameDay($today)) {
                                    $validityStatus = 'Expiring Today';
                                } elseif ($quotation->valid_till <= $in7Days) {
                                    $validityStatus = 'Expiring Soon';
                                }
                            }

                            $hasInvoice = $quotation->invoices && $quotation->invoices->count() > 0;
                            $invoice = $hasInvoice ? $quotation->invoices->first() : null;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.quotations.show', $quotation) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">
                                    {{ $quotation->quotation_no }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $quotation->customer->name ?? 'N/A' }}</div>
                                @if($quotation->customer && $quotation->customer->company_name)
                                    <div class="text-muted small">{{ $quotation->customer->company_name }}</div>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $quotation->date->format('d M Y') }}</td>
                            <td>
                                <div class="fw-medium text-dark">{{ $quotation->valid_till->format('d M Y') }}</div>
                                @if(!$isFinal)
                                    @if($validityStatus == 'Expired')
                                        <span class="badge rounded-pill px-2 py-0" style="background:#fee2e2; color:#991b1b; font-size:0.65rem; border:1px solid #fca5a5;">
                                            <i class="fas fa-times-circle me-1"></i> Expired ({{ abs($daysDiff) }}d ago)
                                        </span>
                                    @elseif($validityStatus == 'Expiring Today')
                                        <span class="badge rounded-pill px-2 py-0" style="background:#fef3c7; color:#b45309; font-size:0.65rem; border:1px solid #fcd34d;">
                                            <i class="fas fa-bell me-1"></i> Expiring Today!
                                        </span>
                                    @elseif($validityStatus == 'Expiring Soon')
                                        <span class="badge rounded-pill px-2 py-0" style="background:#fffbeb; color:#d97706; font-size:0.65rem; border:1px solid #fde68a;">
                                            <i class="fas fa-hourglass-half me-1"></i> {{ $daysDiff }} days left
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2 py-0" style="background:#f0fdf4; color:#15803d; font-size:0.65rem; border:1px solid #bbf7d0;">
                                            <i class="fas fa-check-circle me-1"></i> Valid ({{ $daysDiff }}d)
                                        </span>
                                    @endif
                                @endif
                                <span class="d-none validity-flag">{{ $validityStatus }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ₹{{ number_format($quotation->total, 2) }}
                            </td>
                            <td class="text-center">
                                @if($quotation->status == 'accepted')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:600; font-size:0.75rem;">Accepted</span>
                                @elseif($quotation->status == 'sent')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; font-weight:600; font-size:0.75rem;">Sent</span>
                                @elseif($quotation->status == 'rejected')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:600; font-size:0.75rem;">Rejected</span>
                                @else
                                    <span class="badge rounded-pill px-3 py-1" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600; font-size:0.75rem;">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($hasInvoice)
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="badge rounded-pill text-decoration-none px-3 py-1 shadow-sm" style="background:#10b981; color:#fff;" title="View GST Bill #{{ $invoice->invoice_no }}">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> #{{ $invoice->invoice_no }}
                                    </a>
                                @else
                                    <form action="{{ route('admin.quotations.convert-to-invoice', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Convert Quotation #{{ $quotation->quotation_no }} to GST Bill?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-exchange-alt me-1"></i> Convert to Bill
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-sm btn-light text-primary border" title="View Quotation">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-sm btn-light text-danger border" title="Download PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <!-- WhatsApp Reminder Button -->
                                    <button type="button" class="btn btn-sm btn-light text-success border open-quote-wa-modal"
                                            data-phone="{{ $quotation->customer->phone ?? '' }}"
                                            data-name="{{ $quotation->customer->name ?? 'Customer' }}"
                                            data-no="{{ $quotation->quotation_no }}"
                                            data-date="{{ $quotation->date->format('d M Y') }}"
                                            data-valid="{{ $quotation->valid_till->format('d M Y') }}"
                                            data-total="₹{{ number_format($quotation->total, 2) }}"
                                            data-pdf="{{ route('admin.quotations.pdf', $quotation) }}"
                                            title="Send Expiry / Validity Follow-up on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    @if(in_array($quotation->status, ['accepted', 'rejected']) || $hasInvoice)
                                        <span class="btn btn-sm btn-light text-muted border disabled" title="Status locked">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @else
                                        <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-sm btn-light text-warning border" title="Change Status">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this quotation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger border" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info text-center m-4 py-4">
                <i class="fas fa-info-circle fa-2x mb-2 text-primary"></i>
                <h5>No Quotations Found</h5>
                <p class="text-muted">Start creating quotations to see analytics and manage invoices.</p>
                <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Quotation
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- WhatsApp Follow-up Reminder Modal for Quotations -->
<div class="modal fade" id="quoteWhatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Quotation Validity Reminder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-2 bg-light rounded border text-muted small d-flex align-items-center">
                    <i class="fas fa-headset text-success me-2 fs-5"></i>
                    <div>
                        <strong>Sender / Helpline Number:</strong> <span class="badge bg-success">+91 9099916179</span>
                        <div class="small">Metric Qube Energy Pvt. Ltd.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer WhatsApp Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                        <input type="tel" class="form-control" id="modalQuoteWaPhone" placeholder="Enter 10-digit mobile number">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reminder Message Preview</label>
                    <textarea class="form-control font-monospace" id="modalQuoteWaMessage" rows="10" style="font-size: 0.85rem;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalQuoteDownloadPdfBtn" class="btn btn-outline-danger me-auto" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalQuoteSendWaBtn">
                    <i class="fab fa-whatsapp me-1"></i> Send via WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    @if($quotations->count() > 0)
    const table = $('#quotationsTable').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [-1, -2] }
        ]
    });

    // Filter by Quotation Status
    $('.filter-btn').on('click', function() {
        $('.filter-btn, .filter-validity-btn').removeClass('active');
        $(this).addClass('active');
        const status = $(this).data('status');
        table.column(3).search(''); // clear validity
        table.column(5).search(status).draw();
    });

    // Filter by Validity Status
    $('.filter-validity-btn').on('click', function() {
        $('.filter-btn, .filter-validity-btn').removeClass('active');
        $(this).addClass('active');
        const validity = $(this).data('validity');
        table.column(5).search(''); // clear status
        table.column(3).search(validity).draw();
    });

    // Validity Card Click helper
    window.filterValidity = function(val) {
        $('.filter-btn, .filter-validity-btn').removeClass('active');
        table.column(5).search('');
        table.column(3).search(val).draw();
    };

    // WhatsApp Follow-up Reminder Modal
    $(document).on('click', '.open-quote-wa-modal', function() {
        const phone = $(this).data('phone') || '';
        const name  = $(this).data('name') || 'Customer';
        const no    = $(this).data('no') || '';
        const date  = $(this).data('date') || '';
        const valid = $(this).data('valid') || '';
        const total = $(this).data('total') || '₹0.00';
        const pdf   = $(this).data('pdf') || '';

        $('#modalQuoteWaPhone').val(phone);
        $('#modalQuoteDownloadPdfBtn').attr('href', pdf);

        const msg = `*QUOTATION VALIDITY REMINDER*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nGreetings from Metric Qube Energy!\n\nThis is a gentle reminder regarding your Quotation:\n\n📄 *Quotation No:* ${no}\n📅 *Issued Date:* ${date}\n⏰ *Valid Till:* ${valid}\n💰 *Total Amount:* ${total}\n\nKindly review and confirm your order approval before the quotation validity expires.\n\nFor any customization, queries, or approvals, please contact our helpline: *+91 9099916179*.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

        $('#modalQuoteWaMessage').val(msg);
        const waModal = new bootstrap.Modal(document.getElementById('quoteWhatsappModal'));
        waModal.show();
    });

    $('#modalQuoteSendWaBtn').on('click', function() {
        let phone = $('#modalQuoteWaPhone').val().replace(/[^0-9]/g, '');
        const message = encodeURIComponent($('#modalQuoteWaMessage').val());
        
        if (phone.length === 10) {
            phone = '91' + phone;
        }
        
        if (!phone || phone.length < 10) {
            alert('Please enter a valid 10-digit mobile number.');
            return;
        }
        
        window.open('https://api.whatsapp.com/send?phone=' + phone + '&text=' + message, '_blank');
    });
    @endif
});
</script>
@endpush
@endsection
