@extends('layouts.app')

@section('title', 'Invoices')

@section('breadcrumb')
<li class="breadcrumb-item active">Invoices</li>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i>Invoice Management</h2>
        <div>
            <button type="button" class="btn btn-outline-success me-2 d-none" id="bulkPayBtn" data-bs-toggle="modal" data-bs-target="#bulkPaymentModal">
                <i class="fas fa-money-bill-wave me-1"></i> Bulk Pay Selected (<span id="selectedCount">0</span>)
            </button>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Invoice
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Invoices</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $invoices->count() }}</div>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Billed</div>
                            <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($invoices->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f1f5f9; color: #475569;">
                            <i class="fas fa-receipt fa-lg"></i>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Received</div>
                            <div class="fs-4 fw-bold text-success mt-1">₹{{ number_format($invoices->sum('paid_amount'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Balance Outstanding</div>
                            <div class="fs-4 fw-bold text-danger mt-1">₹{{ number_format($invoices->sum('total') - $invoices->sum('paid_amount'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #fff1f2; color: #e11d48;">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Invoice Type</label>
                    <select name="invoice_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="without_gst" {{ request('invoice_type') == 'without_gst' ? 'selected' : '' }}>Without GST Bill</option>
                        <option value="proforma" {{ request('invoice_type') == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-light btn-sm border"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="invoicesTable" class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr class="text-secondary small fw-bold">
                            <th width="30px" class="no-export ps-4"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                            <th class="py-3">Invoice #</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Due Date</th>
                            <th class="text-end py-3">Total</th>
                            <th class="text-end py-3">Paid</th>
                            <th class="text-end py-3">Balance Due</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td class="no-export ps-4">
                                @if($invoice->balance_due > 0)
                                    <input type="checkbox" class="form-check-input invoice-checkbox" value="{{ $invoice->id }}" data-balance="{{ $invoice->balance_due }}">
                                @else
                                    <input type="checkbox" class="form-check-input" disabled>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">{{ $invoice->invoice_no }}</a>
                                <br>
                                @if($invoice->invoice_type === 'without_gst')
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">Without GST</span>
                                @elseif($invoice->invoice_type === 'proforma')
                                    <span class="badge bg-info" style="font-size: 0.65rem;">Proforma</span>
                                @else
                                    <span class="badge bg-primary" style="font-size: 0.65rem;">Tax Invoice</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $invoice->customer->name ?? 'N/A' }}</div>
                                @if($invoice->customer && $invoice->customer->company_name)
                                    <div class="text-muted small">{{ $invoice->customer->company_name }}</div>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td>
                                <span class="text-muted small">{{ $invoice->due_date->format('d M Y') }}</span>
                                @if($invoice->payment_status !== 'paid' && $invoice->due_date < now())
                                <span class="badge rounded-pill px-2 py-0 ms-1" style="background:#fee2e2; color:#991b1b; font-size:0.65rem; border:1px solid #fca5a5;">Overdue</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($invoice->balance_due, 2) }}
                            </td>
                            <td class="text-center">
                                @if($invoice->payment_status == 'paid')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:600; font-size:0.75rem;">Paid</span>
                                @elseif($invoice->payment_status == 'partial')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fcd34d; font-weight:600; font-size:0.75rem;">Partial</span>
                                @else
                                    <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:600; font-size:0.75rem;">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-light text-primary border" title="View GST Invoice">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm btn-light text-danger border" title="Download GST Bill PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-success border open-wa-modal" 
                                            data-phone="{{ $invoice->customer->phone ?? '' }}"
                                            data-name="{{ $invoice->customer->name ?? 'Customer' }}"
                                            data-no="{{ $invoice->invoice_no }}"
                                            data-date="{{ $invoice->invoice_date->format('d M Y') }}"
                                            data-total="₹{{ number_format($invoice->total, 2) }}"
                                            data-status="{{ strtoupper($invoice->payment_status) }}"
                                            data-balance="₹{{ number_format($invoice->balance_due, 2) }}"
                                            data-pdf="{{ route('admin.invoices.pdf', $invoice) }}"
                                            title="Send GST Bill on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Bulk Payment Modal -->
<div class="modal fade" id="bulkPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Record Bulk Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.invoices.bulk-mark-paid') }}" method="POST" id="bulkPaymentForm">
                @csrf
                <div class="modal-body">
                    <div id="bulkSelectedIdsContainer"></div>
                    <div class="alert alert-info">
                        <strong>Total Selected Balance: ₹<span id="bulkTotalBalanceDisplay">0.00</span></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="neft">NEFT</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No / Transaction ID</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Mark Selected as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WhatsApp Share Modal for Invoices Index -->
<div class="modal fade" id="indexWhatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send GST Bill on WhatsApp</h5>
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
                        <input type="tel" class="form-control" id="modalWaPhone" placeholder="Enter 10-digit mobile number">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Preview</label>
                    <textarea class="form-control font-monospace" id="modalWaMessage" rows="10" style="font-size: 0.85rem;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalDownloadPdfBtn" class="btn btn-outline-danger me-auto" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalSendWaBtn">
                    <i class="fab fa-whatsapp me-1"></i> Send via WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#invoicesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'excel',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'pdf',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'print',
                exportOptions: { columns: ':not(.no-export)' }
            }
        ],
        order: [[1, 'desc']],
        columnDefs: [
            { targets: 0, orderable: false }
        ]
    });

    // Handle Bulk Selection
    function updateBulkButton() {
        const checkedBoxes = $('.invoice-checkbox:checked');
        const count = checkedBoxes.length;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#bulkPayBtn').removeClass('d-none');
        } else {
            $('#bulkPayBtn').addClass('d-none');
        }

        // Fill modal fields on click
        let totalVal = 0;
        let htmlInputs = '';
        checkedBoxes.each(function() {
            const id = $(this).val();
            const bal = parseFloat($(this).data('balance')) || 0;
            totalVal += bal;
            htmlInputs += `<input type="hidden" name="invoice_ids[]" value="${id}">`;
        });
        $('#bulkTotalBalanceDisplay').text(totalVal.toFixed(2));
        $('#bulkSelectedIdsContainer').html(htmlInputs);
    }

    $('#selectAllCheckbox').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.invoice-checkbox:not(:disabled)').prop('checked', isChecked);
        updateBulkButton();
    });

    $(document).on('change', '.invoice-checkbox', function() {
        updateBulkButton();
    });

    // Handle WhatsApp Share Modal
    $(document).on('click', '.open-wa-modal', function() {
        const phone   = $(this).data('phone') || '';
        const name    = $(this).data('name') || 'Customer';
        const no      = $(this).data('no') || '';
        const date    = $(this).data('date') || '';
        const total   = $(this).data('total') || '₹0.00';
        const status  = $(this).data('status') || 'UNPAID';
        const balance = $(this).data('balance') || '₹0.00';
        const pdf     = $(this).data('pdf') || '';
        const publicBillUrl = window.location.origin + '/bill/' + no;

        $('#modalWaPhone').val(phone);
        $('#modalDownloadPdfBtn').attr('href', pdf);

        const msg = `*TAX INVOICE / GST BILL*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nThank you for your business! Here are your GST Invoice details:\n\n📄 *Invoice No:* ${no}\n📅 *Invoice Date:* ${date}\n💰 *Total Amount:* ${total}\n💳 *Payment Status:* ${status}\n💵 *Balance Due:* ${balance}\n\n📥 *View / Download GST Bill (PDF):*\n${publicBillUrl}\n\nFor any queries or assistance, please contact us at *+91 9099916179*.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

        $('#modalWaMessage').val(msg);
        const waModal = new bootstrap.Modal(document.getElementById('indexWhatsappModal'));
        waModal.show();
    });

    $('#modalSendWaBtn').on('click', function() {
        let phone = $('#modalWaPhone').val().replace(/[^0-9]/g, '');
        const message = encodeURIComponent($('#modalWaMessage').val());
        
        if (phone.length === 10) {
            phone = '91' + phone;
        }
        
        if (!phone || phone.length < 10) {
            alert('Please enter a valid 10-digit mobile number.');
            return;
        }
        
        window.open('https://api.whatsapp.com/send?phone=' + phone + '&text=' + message, '_blank');
    });
});
</script>
@endpush
