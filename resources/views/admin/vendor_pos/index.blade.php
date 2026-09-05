@extends('layouts.admin')

@section('title', 'Vendor POs & GST Bills')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Vendor Purchase Orders & GST Bills</h3>
            <p class="text-muted small mb-0">Manage incoming supplier purchase orders, GST tax invoices, and OCR scanned bills</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendor-pos.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fas fa-plus-circle me-1"></i> Create Vendor PO / Scan Bill
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Vendor POs</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $pos->count() }}</div>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Order Value</div>
                            <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($pos->sum('po_value'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-rupee-sign fa-lg"></i>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Items Procured</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ $pos->sum(fn($p) => $p->items->count()) }} <span class="fs-6 fw-normal text-muted">items</span></div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #fffbeb; color: #d97706;">
                            <i class="fas fa-boxes fa-lg"></i>
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
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">GST Input Credit Tracked</div>
                            <div class="fs-4 fw-bold text-primary mt-1">₹{{ number_format($pos->sum(fn($p) => ($p->igst_amount + $p->cgst_amount + $p->sgst_amount)), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #faf5ff; color: #7c3aed;">
                            <i class="fas fa-percentage fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">All Vendor Purchase Orders</h5>
            <span class="badge bg-light text-muted border">{{ $pos->count() }} Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="vendorPoTable">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr class="text-secondary small fw-bold">
                            <th class="ps-4 py-3">PO / Invoice #</th>
                            <th class="py-3">Supplier / Vendor</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Scope Items</th>
                            <th class="text-end py-3">Total Value</th>
                            <th class="text-center py-3">Bill Photo</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pos as $po)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.vendor-pos.show', $po) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $po->vendor_name ?? ($po->vendor->name ?? 'Supplier') }}</div>
                                @if($po->vendor_gstin)
                                <div class="text-muted small font-monospace">GSTIN: {{ $po->vendor_gstin }}</div>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $po->po_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-1" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600; font-size:0.75rem;">
                                    {{ $po->items->count() }} line items
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($po->grand_total > 0 ? $po->grand_total : $po->po_value, 2) }}</td>
                            <td class="text-center">
                                @if($po->bill_image_path)
                                <a href="{{ asset($po->bill_image_path) }}" target="_blank" class="badge rounded-pill px-3 py-1 text-decoration-none shadow-sm" style="background: #3b82f6; color: #fff;">
                                    <i class="fas fa-image me-1"></i> Attached
                                </a>
                                @else
                                <span class="badge rounded-pill px-2 py-1 text-muted" style="background:#f8fafc; border:1px dashed #cbd5e1;">None</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:600; font-size:0.75rem;">
                                    {{ strtoupper($po->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.vendor-pos.show', $po) }}" class="btn btn-sm btn-light text-primary border" title="View Tax Invoice PO">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.vendor-pos.pdf', $po) }}" class="btn btn-sm btn-light text-danger border" title="Download PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-success border open-wa-modal" 
                                            data-phone="{{ preg_replace('/[^0-9]/', '', $po->vendor->phone ?? '') }}"
                                            data-name="{{ $po->vendor_name ?? ($po->vendor->name ?? 'Vendor') }}"
                                            data-no="{{ $po->po_number }}"
                                            data-date="{{ $po->po_date->format('d M Y') }}"
                                            data-total="₹{{ number_format($po->grand_total > 0 ? $po->grand_total : $po->po_value, 2) }}"
                                            data-pdf="{{ route('admin.vendor-pos.pdf', $po) }}"
                                            title="Send via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
                                <h5>No Vendor Purchase Orders Yet</h5>
                                <p class="small">Upload a bill photo or create a new Vendor PO based on GST Tax Invoice format.</p>
                                <a href="{{ route('admin.vendor-pos.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                    <i class="fas fa-plus me-1"></i> Create First Vendor PO
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Vendor PO on WhatsApp</h5>
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
                    <label class="form-label fw-semibold">Vendor WhatsApp Mobile Number <span class="text-danger">*</span></label>
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
    $(document).on('click', '.open-wa-modal', function() {
        let phone   = $(this).data('phone') || '';
        const name    = $(this).data('name') || 'Vendor';
        const no      = $(this).data('no') || '';
        const date    = $(this).data('date') || '';
        const total   = $(this).data('total') || '₹0.00';
        const pdf     = $(this).data('pdf') || '';

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }

        $('#modalWaPhone').val(phone);
        $('#modalDownloadPdfBtn').attr('href', pdf);

        const msg = `*PURCHASE ORDER*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nPlease find attached the Purchase Order details:\n\n📄 *PO No:* ${no}\n📅 *PO Date:* ${date}\n💰 *Total Value:* ${total}\n\n📥 *Download PDF:*\n${pdf}\n\nPlease confirm receipt and process the order.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

        $('#modalWaMessage').val(msg);
        const waModal = new bootstrap.Modal(document.getElementById('whatsappModal'));
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
