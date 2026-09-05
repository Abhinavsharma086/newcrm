@extends('layouts.app')

@section('title', $section === 'technical' ? 'LMC & Technical Details' : ($section === 'mlc' ? 'MLC (Meter) Details' : 'Customer Registrations'))

@section('breadcrumb')
<li class="breadcrumb-item active">{{ $section === 'technical' ? 'LMC & Technical Details' : ($section === 'mlc' ? 'MLC (Meter) Details' : 'Customer Registrations') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $section === 'technical' ? 'LMC & Technical Details' : ($section === 'mlc' ? 'MLC (Meter) Details' : 'Customer Registration Details') }}</h2>
        <div>
            @if($section === 'registration')
                <button type="button" class="btn btn-warning me-2 text-white" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-upload"></i> Bulk Import
                </button>
                <a href="{{ route('admin.customers.export') }}" class="btn btn-success me-2">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Customer
                </a>
            @else
                <a href="{{ route('admin.customers.export') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            @endif
        </div>
    </div>

    <!-- Assignment Settings (Only relevant for Registration or General status) -->
    @if($section === 'registration')
    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-2">
            <form action="{{ route('admin.customers.assignment-settings') }}" method="POST" class="row align-items-center g-3">
                @csrf
                <div class="col-auto">
                    <span class="fw-bold"><i class="fas fa-robot text-primary"></i> Auto-Assignment Mode:</span>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="mode" onchange="this.form.submit()">
                        <option value="sequence" {{ \App\Models\CompanySetting::get('customer_assignment_mode', 'sequence') == 'sequence' ? 'selected' : '' }}>
                            Sequence-wise (Round Robin)
                        </option>
                        <option value="location" {{ \App\Models\CompanySetting::get('customer_assignment_mode', 'sequence') == 'location' ? 'selected' : '' }}>
                            Location-wise (Society-based)
                        </option>
                        <option value="workload" {{ \App\Models\CompanySetting::get('customer_assignment_mode', 'sequence') == 'workload' ? 'selected' : '' }}>
                            Workload-wise (Unresolved Tickets)
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <small class="text-muted">(Location mode matches customer's society to employee's assigned societies, falling back to Round-Robin)</small>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Filters Settings -->
    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-2">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <span class="fw-bold"><i class="fas fa-filter text-success"></i> Filter by Society:</span>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" id="societyFilter" style="min-width: 220px;">
                        <option value="">All Societies</option>
                        @foreach($societies as $soc)
                            <option value="{{ $soc->name }}">{{ $soc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <x-card>
        <table id="customersTable" class="table table-hover align-middle">
            @if($section === 'registration')
                <thead>
                    <tr>
                        <th>CRN Number</th>
                        <th>Name</th>
                        <th>Society</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                        <th>Reg. Amount</th>
                        <th>Payment Mode / Ref</th>
                        <th class="text-center">KYC Docs</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td><code>{{ $customer->crn_no ?? 'N/A' }}</code></td>
                        <td class="fw-medium text-dark">{{ $customer->name }}</td>
                        <td>{{ $customer->society ?? 'N/A' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->registration_date ? $customer->registration_date->format('d M Y') : 'N/A' }}</td>
                        <td class="text-success fw-bold">₹{{ number_format($customer->reg_amount ?? 0, 2) }}</td>
                        <td>
                            @if($customer->mode_of_payment)
                                <span class="badge bg-light text-dark border">{{ $customer->mode_of_payment }}</span>
                                <small class="d-block text-muted">{{ $customer->payment_ref_no }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <i class="fas fa-id-card {{ $customer->primary_id_file ? 'text-success' : 'text-muted opacity-40' }}" title="Aadhaar: {{ $customer->primary_id_number ?? 'Missing' }}"></i>
                                <i class="fas fa-user-circle {{ $customer->passport_photo ? 'text-success' : 'text-muted opacity-40' }}" title="Photo"></i>
                                <i class="fas fa-file-invoice {{ $customer->address_proof_file ? 'text-success' : 'text-muted opacity-40' }}" title="Address Proof"></i>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-light text-primary border shadow-sm" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light text-success border shadow-sm open-wa-modal" 
                                        data-phone="{{ preg_replace('/[^0-9]/', '', $customer->phone) }}"
                                        data-name="{{ $customer->name }}"
                                        data-crn="{{ $customer->crn_no ?? 'N/A' }}"
                                        data-type="registration"
                                        title="Send CRM Details via WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light text-info border shadow-sm open-schedule-modal" 
                                        data-customer-id="{{ $customer->id }}"
                                        data-customer-name="{{ $customer->name }}"
                                        title="Schedule Appointment">
                                    <i class="fas fa-calendar-check"></i>
                                </button>
                                <a href="{{ route('admin.customers.edit', [$customer, 'section' => 'registration']) }}" class="btn btn-sm btn-light text-warning border shadow-sm" title="Edit Registration">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-light text-danger border shadow-sm" onclick="deleteCustomer({{ $customer->id }}, 'registration')" title="Delete Customer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            @else
                <thead>
                    <tr>
                        <th>CRN Number</th>
                        <th>LMC ID</th>
                        <th>Name</th>
                        <th>Society</th>
                        <th>Meter No</th>
                        <th>LMC Date</th>
                        <th>RFC Date</th>
                        <th>JMR Date</th>
                        <th>Conversion Date</th>
                        <th>Stage</th>
                        <th>Assigned To</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td><code>{{ $customer->crn_no ?? 'N/A' }}</code></td>
                        <td>
                            @if($customer->lmc_id)
                                <span class="badge bg-dark">{{ $customer->lmc_id }}</span>
                            @else
                                <span class="text-muted small">Pending LMC</span>
                            @endif
                        </td>
                        <td class="fw-medium text-dark">{{ $customer->name }}</td>
                        <td>{{ $customer->society ?? 'N/A' }}</td>
                        <td><code>{{ $customer->meter_no ?? 'N/A' }}</code></td>
                        <td>{{ $customer->lmc_date ? $customer->lmc_date->format('d M Y') : 'Pending' }}</td>
                        <td>{{ $customer->rfc_date ? $customer->rfc_date->format('d M Y') : 'Pending' }}</td>
                        <td>{{ $customer->jmr_date ? $customer->jmr_date->format('d M Y') : 'Pending' }}</td>
                        <td>
                            @if($customer->conversion_date)
                                <span class="text-success fw-bold">{{ $customer->conversion_date->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">Pending</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->stage_badge_color }}">{{ $customer->customer_stage ?? $customer->auto_stage }}</span>
                        </td>
                        <td>
                            @if($customer->assignee)
                                <span class="badge bg-primary">{{ $customer->assignee->name }}</span>
                            @else
                                <span class="badge bg-secondary">Unassigned</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-light text-primary border shadow-sm" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light text-success border shadow-sm open-wa-modal" 
                                        data-phone="{{ preg_replace('/[^0-9]/', '', $customer->phone) }}"
                                        data-name="{{ $customer->name }}"
                                        data-crn="{{ $customer->crn_no ?? 'N/A' }}"
                                        data-type="technical"
                                        data-lmc="{{ $customer->lmc_id ?? 'N/A' }}"
                                        data-meter="{{ $customer->meter_no ?? 'N/A' }}"
                                        title="Send CRM Details via WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light text-info border shadow-sm open-schedule-modal" 
                                        data-customer-id="{{ $customer->id }}"
                                        data-customer-name="{{ $customer->name }}"
                                        title="Schedule Appointment">
                                    <i class="fas fa-calendar-check"></i>
                                </button>
                                <a href="{{ route('admin.customers.edit', [$customer, 'section' => 'technical']) }}" class="btn btn-sm btn-light text-warning border shadow-sm" title="Edit Technical/LMC">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    </x-card>

    <!-- Import Modal -->
    @if($section === 'registration')
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.customers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Bulk Import Customers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please upload an Excel sheet (<strong>.xlsx</strong>, <strong>.xls</strong>, or <strong>.csv</strong>) matching the exact columns of the registration template.
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose Excel File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign all to Employee (Optional)</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">-- Leave Unassigned --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }} ({{ $employee->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-upload"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    @endif

    <!-- WhatsApp Modal -->
    <div class="modal fade" id="whatsappModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send CRM Details</h5>
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
                        <textarea class="form-control font-monospace" id="modalWaMessage" rows="8" style="font-size: 0.85rem;"></textarea>
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

    <!-- Schedule Appointment Modal -->
    <div class="modal fade" id="scheduleAppointmentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="customer_id" id="modalScheduleCustomerId">
                    <input type="hidden" name="appointment_status" value="scheduled">
                    <input type="hidden" name="source" value="Customer List">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Schedule Appointment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer</label>
                            <input type="text" class="form-control bg-light" id="modalScheduleCustomerName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="modalScheduleTitle" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="appointment_date" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="appointment_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign To (Executive)</label>
                            <select class="form-select" name="assigned_to" id="modalScheduleAssignee">
                                <option value="">-- Select Executive --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" data-phone="{{ $emp->phone }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign Technician</label>
                            <select class="form-select" name="assigned_technician">
                                <option value="">-- Select Technician --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendWaToEmployee" checked>
                            <label class="form-check-label text-muted small" for="sendWaToEmployee">
                                Send WhatsApp to Employee
                            </label>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveAppointmentBtn">
                                <i class="fas fa-save me-1"></i> Save Appointment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#customersTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });

    $('#societyFilter').on('change', function() {
        var val = $(this).val();
        table.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
    });

    // WhatsApp Modal Logic
    $(document).on('click', '.open-wa-modal', function() {
        let phone   = $(this).data('phone') || '';
        const name  = $(this).data('name') || 'Customer';
        const crn   = $(this).data('crn') || 'N/A';
        const type  = $(this).data('type') || 'registration';
        const lmc   = $(this).data('lmc') || 'N/A';
        const meter = $(this).data('meter') || 'N/A';

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }
        $('#modalWaPhone').val(phone);

        let msg = '';
        if (type === 'technical') {
            msg = `*LMC & TECHNICAL DETAILS UPDATE*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nYour technical connection details have been updated:\n\n📄 *CRN No:* ${crn}\n🔧 *LMC ID:* ${lmc}\n⚡ *Meter No:* ${meter}\n\nFor any support or inquiries, please feel free to reach out to us.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;
        } else {
            msg = `*CUSTOMER REGISTRATION DETAILS*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nWelcome to Metric Qube Energy! Your registration is complete.\n\n📄 *CRN No:* ${crn}\n\nOur CRM team is processing your details. For any support or inquiries, please feel free to reach out to us.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;
        }

        $('#modalWaMessage').val(msg);
        $('#whatsappModal .modal-title').html('<i class="fab fa-whatsapp me-2"></i>Send CRM Details');
        
        const waModal = new bootstrap.Modal(document.getElementById('whatsappModal'));
        waModal.show();
    });

    $(document).on('click', '.open-schedule-modal', function() {
        const customerId = $(this).data('customer-id');
        const customerName = $(this).data('customer-name');
        
        $('#modalScheduleCustomerId').val(customerId);
        $('#modalScheduleCustomerName').val(customerName);
        $('#modalScheduleTitle').val('Visit to: ' + customerName);
        
        const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleAppointmentModal'));
        scheduleModal.show();
    });

    // Handle form submission and optional WhatsApp send to employee
    $('#scheduleAppointmentModal form').on('submit', function(e) {
        if ($('#sendWaToEmployee').is(':checked')) {
            const assigneeOption = $('#modalScheduleAssignee option:selected');
            const empPhone = assigneeOption.data('phone');
            const empName = assigneeOption.text();
            
            if (empPhone) {
                let cleanPhone = empPhone.toString().replace(/[^0-9]/g, '');
                if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
                
                const customerName = $('#modalScheduleCustomerName').val();
                const appDate = $('input[name="appointment_date"]').val();
                const appTime = $('input[name="appointment_time"]').val();
                
                const msg = `*NEW APPOINTMENT ASSIGNED*\n----------------------------------\nHi *${empName}*,\n\nYou have been assigned a new customer visit.\n\n👤 *Customer:* ${customerName}\n📅 *Date:* ${appDate}\n⏰ *Time:* ${appTime}\n\nPlease check the CRM admin panel for full details.\n\n*Metric Qube Energy Pvt. Ltd.*`;
                
                window.open('https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(msg), '_blank');
            }
        }
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

function deleteCustomer(id, section) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the customer registration record!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ED1C24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/customers/' + id + '?section=' + section;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
