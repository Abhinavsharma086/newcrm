@extends('layouts.app')
@section('title', 'Leads')
@section('breadcrumb')
<li class="breadcrumb-item active">Leads</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lead Management</h2>
        <a href="{{ route('admin.leads.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Lead
        </a>
    </div>

    <x-card>
        <table id="leadsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Customer</th>
                    <th>Stage</th>
                    <th>Source</th>
                    <th>Value</th>
                    <th>Assigned To</th>
                    <th>Follow-up</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $lead)
                <tr>
                    <td>{{ $lead->title }}</td>
                    <td>{{ $lead->customer ? $lead->customer->name : '—' }}</td>
                    <td>
                        @php
                            $stageColors = [
                                'new' => 'secondary',
                                'contacted' => 'info',
                                'qualified' => 'primary',
                                'converted' => 'success',
                                'lost' => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $stageColors[$lead->stage] ?? 'secondary' }}">
                            {{ ucfirst($lead->stage) }}
                        </span>
                    </td>
                    <td>{{ $lead->source }}</td>
                    <td>{{ $lead->value ? '₹' . number_format($lead->value, 2) : '—' }}</td>
                    <td>{{ $lead->assignee ? $lead->assignee->name : 'Unassigned' }}</td>
                    <td>{{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-info"><i class="fas fa-eye text-white"></i></a>
                            <button type="button" class="btn btn-success open-wa-modal" 
                                    data-phone="{{ preg_replace('/[^0-9]/', '', $lead->customer ? $lead->customer->phone : '') }}"
                                    data-name="{{ $lead->customer ? $lead->customer->name : 'Customer' }}"
                                    title="Send WhatsApp Message">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-warning"><i class="fas fa-edit text-white"></i></a>
                            <button class="btn btn-danger" onclick="deleteLead({{ $lead->id }})"><i class="fas fa-trash text-white"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>

    <!-- WhatsApp Modal -->
    <div class="modal fade" id="whatsappModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Message to Lead</h5>
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
                        <label class="form-label fw-semibold">Lead WhatsApp Mobile Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                            <input type="tel" class="form-control" id="modalWaPhone" placeholder="Enter 10-digit mobile number">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message Preview</label>
                        <textarea class="form-control font-monospace" id="modalWaMessage" rows="6" style="font-size: 0.85rem;"></textarea>
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

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#leadsTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'pdf', 'print'] });

    // WhatsApp Modal Logic
    $(document).on('click', '.open-wa-modal', function() {
        let phone   = $(this).data('phone') || '';
        const name  = $(this).data('name') || 'Customer';

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }
        $('#modalWaPhone').val(phone);

        const msg = `*GREETINGS FROM METRIC QUBE ENERGY*\n----------------------------------\nDear *${name}*,\n\nThank you for reaching out to us. We would like to discuss your requirement.\n\nPlease let us know a suitable time to connect.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

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
function deleteLead(id) {
    Swal.fire({ title: 'Delete Lead?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ED1C24', confirmButtonText: 'Yes, delete!' })
    .then(r => {
        if (r.isConfirmed) {
            let f = document.createElement('form');
            f.method = 'POST'; f.action = '/admin/leads/' + id;
            f.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(f); f.submit();
        }
    });
}
</script>
@endpush
