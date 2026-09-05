@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Client POs / Work Orders</h2>
        <a href="{{ route('admin.client-pos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Receive Client PO
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Client</th>
                        <th>Site</th>
                        <th>PO Date</th>
                        <th>PO Value</th>
                        <th>Retention</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        <td class="fw-bold">{{ $po->po_number }}</td>
                        <td>{{ $po->client->name }}</td>
                        <td>{{ $po->site_name ?? '-' }}</td>
                        <td>{{ $po->po_date->format('d M Y') }}</td>
                        <td class="fw-bold">₹{{ number_format($po->po_value, 2) }}</td>
                        <td>{{ $po->retention_percent }}%</td>
                        <td>
                            <span class="badge bg-success">{{ strtoupper($po->status) }}</span>
                        </td>
                        <td>
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('admin.client-pos.show', $po) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye me-1"></i> Track Progress
                                </a>
                                <button type="button" class="btn btn-sm btn-success open-wa-modal" 
                                        data-phone="{{ preg_replace('/[^0-9]/', '', $po->client->phone ?? '') }}"
                                        data-name="{{ $po->client->name ?? 'Client' }}"
                                        data-no="{{ $po->po_number }}"
                                        data-date="{{ $po->po_date->format('d M Y') }}"
                                        data-total="₹{{ number_format($po->po_value, 2) }}"
                                        title="Send via WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No Client POs recorded yet.</td>
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
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Client PO Acknowledgment</h5>
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
                    <label class="form-label fw-semibold">Client WhatsApp Mobile Number <span class="text-danger">*</span></label>
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
        const name    = $(this).data('name') || 'Client';
        const no      = $(this).data('no') || '';
        const date    = $(this).data('date') || '';
        const total   = $(this).data('total') || '₹0.00';

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }

        $('#modalWaPhone').val(phone);

        const msg = `*CLIENT PO ACKNOWLEDGMENT*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nWe acknowledge the receipt of your Work Order / PO:\n\n📄 *PO No:* ${no}\n📅 *Date:* ${date}\n💰 *PO Value:* ${total}\n\nOur team is reviewing the details and will start processing it shortly.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

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
