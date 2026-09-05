@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Vendor / Subcontractor Invoices (Bills)</h2>
        <a href="{{ route('admin.vendor-invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Record Vendor Bill
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Bill / Invoice #</th>
                        <th>Vendor</th>
                        <th>Associated PO</th>
                        <th>Invoice Date</th>
                        <th>Net Payable</th>
                        <th>Matching Status</th>
                        <th>Approval Status</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $bill)
                    <tr>
                        <td class="fw-bold">{{ $bill->invoice_number }}</td>
                        <td>{{ $bill->vendor->name }}</td>
                        <td>
                            @if($bill->vendorPo)
                            <a href="{{ route('admin.vendor-pos.show', $bill->vendorPo) }}" class="fw-bold text-decoration-none">
                                {{ $bill->vendorPo->po_number }}
                            </a>
                            @else
                            <span class="text-muted">Direct (No PO)</span>
                            @endif
                        </td>
                        <td>{{ $bill->invoice_date->format('d M Y') }}</td>
                        <td class="fw-bold">₹{{ number_format($bill->net_payable, 2) }}</td>
                        <td>
                            @if($bill->matching_status === 'matched')
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>MATCHED</span>
                            @else
                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>FLAGGED</span>
                            @endif
                        </td>
                        <td>
                            @if($bill->approval_status === 'approved')
                            <span class="badge bg-success">APPROVED</span>
                            @else
                            <span class="badge bg-warning text-dark">PENDING</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $bill->payment_status === 'paid' ? 'success' : 'danger' }}">{{ strtoupper($bill->payment_status) }}</span>
                        </td>
                        <td>
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('admin.vendor-invoices.show', $bill) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye me-1"></i> Verify / Audit
                                </a>
                                <button type="button" class="btn btn-sm btn-success open-wa-modal" 
                                        data-phone="{{ preg_replace('/[^0-9]/', '', $bill->vendor->phone ?? '') }}"
                                        data-name="{{ $bill->vendor->name ?? 'Vendor' }}"
                                        data-no="{{ $bill->invoice_number }}"
                                        data-date="{{ $bill->invoice_date->format('d M Y') }}"
                                        data-total="₹{{ number_format($bill->net_payable, 2) }}"
                                        title="Send via WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No Vendor Invoices recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- WhatsApp Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Vendor Invoice Update</h5>
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

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }

        $('#modalWaPhone').val(phone);

        const msg = `*VENDOR INVOICE UPDATE*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nWe have received your invoice. Here are the details:\n\n📄 *Invoice No:* ${no}\n📅 *Date:* ${date}\n💰 *Net Payable:* ${total}\n\nWe will verify and process the payment soon.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

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
