@extends('layouts.app')

@section('title', 'Confirmed Appointments')

@section('breadcrumb')
<li class="breadcrumb-item active">Confirmed Appointments</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Confirmed Appointment & Scheduling</h2>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
            <i class="fas fa-calendar-plus"></i> Schedule Manual Appointment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabs for Directory, AI Scheduler, Denied Cases, Conversion Done Cases -->
    <ul class="nav nav-pills mb-4" id="appointmentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="directory-tab" data-bs-toggle="pill" data-bs-target="#directory" type="button" role="tab">
                <i class="fas fa-list me-1"></i> Confirmed Appointments Directory
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ai-scheduler-tab" data-bs-toggle="pill" data-bs-target="#ai-scheduler" type="button" role="tab">
                <span class="badge bg-danger me-1">AI</span> AI Smart Scheduler
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="conversions-tab" data-bs-toggle="pill" data-bs-target="#conversions" type="button" role="tab">
                <i class="fas fa-check-double me-1"></i> Conversion Done Cases
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-danger" id="denied-tab" data-bs-toggle="pill" data-bs-target="#denied" type="button" role="tab">
                <i class="fas fa-ban me-1"></i> Customer Denied Cases
            </button>
        </li>
    </ul>

    <div class="tab-content" id="appointmentTabsContent">
        <!-- Tab 1: Directory (Scheduled / Visited / Confirmed) -->
        <div class="tab-pane fade show active" id="directory" role="tabpanel">
            <x-card>
                <table id="appointmentsTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Society</th>
                            <th>Assigned Exec (Marketing)</th>
                            <th>Field Technician</th>
                            <th>Appointment DateTime</th>
                            <th>Follow Up</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments->whereNotIn('appointment_status', ['converted', 'denied']) as $app)
                        <tr>
                            <td>
                                <strong>{{ $app->customer?->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $app->customer?->phone }}</small>
                            </td>
                            <td>{{ $app->customer?->society ?? 'N/A' }}</td>
                            <td>
                                @if($app->assignee)
                                    <span class="badge bg-info text-white"><i class="fas fa-user-tag"></i> {{ $app->assignee->name }}</span>
                                @else
                                    <span class="badge bg-secondary">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($app->technician)
                                    <span class="badge bg-primary text-white"><i class="fas fa-wrench"></i> {{ $app->technician->name }}</span>
                                @else
                                    <span class="badge bg-secondary">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="fas fa-calendar-alt"></i> 
                                    {{ $app->appointment_date ? $app->appointment_date->format('d M Y') : 'Not Set' }}
                                </span>
                                @if($app->appointment_time)
                                    <br><small class="text-muted"><i class="fas fa-clock"></i> {{ $app->appointment_time }}</small>
                                @endif
                            </td>
                            <td>
                                @if($app->follow_up_date)
                                    <small class="d-block text-warning fw-bold"><i class="fas fa-redo"></i> {{ $app->follow_up_date->format('d M Y') }}</small>
                                    <small class="text-muted">{{ Str::limit($app->follow_up_note, 25) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $app->status_badge_color }}">
                                    {{ ucfirst($app->appointment_status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.appointments.show', $app) }}" class="btn btn-sm btn-light text-primary border shadow-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-success border shadow-sm open-wa-modal" 
                                            data-phone="{{ preg_replace('/[^0-9]/', '', $app->customer?->phone ?? '') }}"
                                            data-name="{{ $app->customer?->name ?? 'Customer' }}"
                                            data-date="{{ $app->appointment_date ? $app->appointment_date->format('d M Y') : 'Not Set' }}"
                                            data-time="{{ $app->appointment_time ?? 'Not Set' }}"
                                            data-tech="{{ $app->technician?->name ?? 'Our Executive' }}"
                                            title="Send Appointment Confirmation via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-info border shadow-sm open-reschedule-modal" 
                                            title="Reschedule Appointment" 
                                            data-id="{{ $app->id }}"
                                            data-customer="{{ $app->customer?->name ?? 'N/A' }}"
                                            data-date="{{ $app->appointment_date ? $app->appointment_date->format('Y-m-d') : '' }}"
                                            data-time="{{ $app->appointment_time ?? '' }}"
                                            data-exec="{{ $app->assigned_to ?? '' }}"
                                            data-tech="{{ $app->assigned_technician ?? '' }}"
                                            data-notes="{{ $app->notes ?? '' }}">
                                        <i class="fas fa-calendar-check"></i>
                                    </button>
                                    <a href="{{ route('admin.appointments.edit', $app) }}" class="btn btn-sm btn-light text-warning border shadow-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-light text-danger border shadow-sm" title="Mark Denied" data-bs-toggle="modal" data-bs-target="#denyModal-{{ $app->id }}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </div>

                                <!-- Denial Modal -->
                                <div class="modal fade" id="denyModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.appointments.deny', $app) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Customer Denied Case Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-dark">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Person Who Denied <span class="text-danger">*</span></label>
                                                        <input type="text" name="denied_by_person" class="form-control" placeholder="e.g. Owner, Tenant, Relative" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Reason for Denying <span class="text-danger">*</span></label>
                                                        <textarea name="denial_reason" class="form-control" rows="3" placeholder="Explain the customer's response/reason" required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Photograph of Visit</label>
                                                        <input type="file" name="denial_photo" class="form-control" accept="image/*">
                                                        <small class="text-muted">Upload photo of gate/house or visit proof</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Further Action Required <span class="text-danger">*</span></label>
                                                        <select name="further_action" class="form-select" required>
                                                            <option value="callback">Callback Later</option>
                                                            <option value="reschedule">Reschedule</option>
                                                            <option value="drop">Drop Lead/Cancel</option>
                                                            <option value="escalate">Escalate to Manager</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Further Action Follow up Date</label>
                                                        <input type="date" name="further_action_date" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Confirm Denial</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>
        </div>

        <!-- Tab 2: AI Smart Scheduler -->
        <div class="tab-pane fade" id="ai-scheduler" role="tabpanel">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white mb-4">
                        <div class="card-body">
                            <h5><i class="fas fa-robot"></i> AI Grouping Engine</h5>
                            <p class="small mb-0">Our AI scans your customer database and groups them based on their **Society**. By scheduling visits in bulk for the same society on the same day, you can save significant travel time for your executives!</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    @if(count($aiGroups) == 0)
                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="fas fa-info-circle me-1"></i> No pending customers or societies require bulk scheduling currently.
                        </div>
                    @else
                        @foreach($aiGroups as $index => $group)
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="mb-0 text-dark"><i class="fas fa-city text-primary me-2"></i> {{ $group['society'] }}</h5>
                                            <span class="badge bg-warning text-dark mt-1">
                                                {{ $group['pending_count'] }} pending appointments out of {{ $group['total_customers'] }} total customers
                                            </span>
                                        </div>
                                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#schedule-{{ $index }}">
                                            <i class="fas fa-magic"></i> AI Bulk Schedule
                                        </button>
                                    </div>

                                    <!-- Collapse Form for bulk scheduling -->
                                    <div class="collapse mt-3" id="schedule-{{ $index }}">
                                        <div class="card card-body bg-light border-0">
                                            <form action="{{ route('admin.appointments.bulk-schedule') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="society" value="{{ $group['society'] }}">
                                                
                                                <div class="row text-dark">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label small fw-bold">Visit Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control form-control-sm" name="appointment_date" required min="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label small fw-bold">Time Slot <span class="text-danger">*</span></label>
                                                        <input type="time" class="form-control form-control-sm" name="appointment_time" required>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label small fw-bold">Assign Exec (Marketing) <span class="text-danger">*</span></label>
                                                        <select class="form-select form-select-sm" name="assigned_to" required>
                                                            @foreach($employees as $emp)
                                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label small fw-bold">Assign Tech (Field Tech)</label>
                                                        <select class="form-select form-select-sm" name="assigned_technician">
                                                            <option value="">-- Optional --</option>
                                                            @foreach($employees as $emp)
                                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        This action will create new appointments for all {{ $group['pending_count'] }} pending customers in this society.
                                                    </small>
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Save Bulk Appointments
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Customer List for verification -->
                                    <div class="mt-2">
                                        <a class="text-decoration-none small text-primary" data-bs-toggle="collapse" href="#customers-{{ $index }}">
                                            <i class="fas fa-chevron-down"></i> View Pending Customers ({{ $group['pending_count'] }})
                                        </a>
                                        <div class="collapse mt-2" id="customers-{{ $index }}">
                                            <ul class="list-group list-group-flush border rounded">
                                                @foreach($group['customers'] as $cust)
                                                    <li class="list-group-item py-1 d-flex justify-content-between align-items-center bg-transparent">
                                                        <span class="small text-dark">{{ $cust->name }}</span>
                                                        <small class="text-muted">{{ $cust->phone }}</small>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 3: Conversion Done Cases -->
        <div class="tab-pane fade" id="conversions" role="tabpanel">
            <x-card>
                <table id="conversionsTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Society</th>
                            <th>LMC Date</th>
                            <th>RFC Date</th>
                            <th>JMR Date</th>
                            <th>Conversion Date</th>
                            <th>MLC Length</th>
                            <th>Extra Amt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments->where('appointment_status', 'converted') as $app)
                        <tr>
                            <td>
                                <strong>{{ $app->customer?->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $app->customer?->phone }}</small>
                            </td>
                            <td>{{ $app->customer?->society ?? 'N/A' }}</td>
                            <td>{{ $app->customer?->lmc_date ? $app->customer->lmc_date->format('d M Y') : '-' }}</td>
                            <td>{{ $app->customer?->rfc_date ? $app->customer->rfc_date->format('d M Y') : '-' }}</td>
                            <td>{{ $app->customer?->jmr_date ? $app->customer->jmr_date->format('d M Y') : '-' }}</td>
                            <td><span class="badge bg-success">{{ $app->customer?->conversion_date ? $app->customer->conversion_date->format('d M Y') : '-' }}</span></td>
                            <td>{{ $app->customer?->mlc_pipe_length ? $app->customer->mlc_pipe_length . ' Mtr' : '-' }}</td>
                            <td>{{ $app->customer?->extra_mlc_amount ? '₹' . number_format($app->customer->extra_mlc_amount, 2) : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $app) }}" class="btn btn-sm btn-info text-white" title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>
        </div>

        <!-- Tab 4: Customer Denied Cases -->
        <div class="tab-pane fade" id="denied" role="tabpanel">
            <x-card>
                <table id="deniedTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Society</th>
                            <th>Person Denied</th>
                            <th>Reason</th>
                            <th>Photo Visit</th>
                            <th>Action Planning</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments->where('appointment_status', 'denied') as $app)
                        <tr>
                            <td>
                                <strong>{{ $app->customer?->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $app->customer?->phone }}</small>
                            </td>
                            <td>{{ $app->customer?->society ?? 'N/A' }}</td>
                            <td><span class="fw-bold text-dark">{{ $app->denied_by_person ?? 'N/A' }}</span></td>
                            <td><small class="text-danger">{{ $app->denial_reason }}</small></td>
                            <td>
                                @if($app->denial_photo)
                                    <a href="{{ asset('storage/' . $app->denial_photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $app->denial_photo) }}" class="img-thumbnail" style="max-height: 50px;" alt="Denial proof">
                                    </a>
                                @else
                                    <span class="text-muted">No Photo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">{{ ucfirst($app->further_action) }}</span>
                                @if($app->further_action_date)
                                    <br><small class="text-muted">Date: {{ $app->further_action_date->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('admin.appointments.reopen', $app) }}" method="POST" onsubmit="return confirm('Do you want to RE-OPEN this appointment and clear denial logs?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-undo"></i> Reopen Visit
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>
        </div>
    </div>

</div>

<!-- WhatsApp Appointment Confirmation Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Appointment Confirmation</h5>
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
                    <textarea class="form-control font-monospace" id="modalWaMessage" rows="9" style="font-size: 0.85rem;"></textarea>
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

<!-- Reschedule Appointment Modal -->
<div class="modal fade" id="rescheduleAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="rescheduleForm" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Reschedule Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer</label>
                        <input type="text" class="form-control bg-light" id="modalRescheduleCustomerName" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="appointment_date" id="modalRescheduleDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="appointment_time" id="modalRescheduleTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign To (Executive)</label>
                        <select class="form-select" name="assigned_to" id="modalRescheduleAssignee">
                            <option value="">-- Select Executive --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-phone="{{ $emp->phone }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Technician</label>
                        <select class="form-select" name="assigned_technician" id="modalRescheduleTechnician">
                            <option value="">-- Select Technician --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" id="modalRescheduleNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendWaToEmployeeReschedule" checked>
                        <label class="form-check-label text-muted small" for="sendWaToEmployeeReschedule">
                            Send WhatsApp to Employee
                        </label>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-save me-1"></i> Update Appointment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#appointmentsTable').DataTable({
        responsive: true
    });
    $('#conversionsTable').DataTable({
        responsive: true
    });
    $('#deniedTable').DataTable({
        responsive: true
    });

    // WhatsApp Modal Logic
    $(document).on('click', '.open-wa-modal', function() {
        let phone   = $(this).data('phone') || '';
        const name  = $(this).data('name') || 'Customer';
        const date  = $(this).data('date') || 'Not Set';
        const time  = $(this).data('time') || 'Not Set';
        const tech  = $(this).data('tech') || 'Our Executive';

        if (phone.length === 12 && phone.startsWith('91')) {
            phone = phone.substring(2);
        }
        $('#modalWaPhone').val(phone);

        const msg = `*APPOINTMENT CONFIRMATION*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nYour appointment has been successfully scheduled.\n\n📅 *Date:* ${date}\n⏰ *Time:* ${time}\n👨‍🔧 *Technician:* ${tech}\n\nOur team member will visit you at the scheduled time. For any changes or support, please contact us.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

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

    // Reschedule Modal Logic
    $(document).on('click', '.open-reschedule-modal', function() {
        const id = $(this).data('id');
        const customer = $(this).data('customer');
        const date = $(this).data('date');
        const time = $(this).data('time');
        const exec = $(this).data('exec');
        const tech = $(this).data('tech');
        const notes = $(this).data('notes');
        
        // Update Form Action Dynamically
        $('#rescheduleForm').attr('action', `/admin/appointments/${id}/quick-reschedule`);
        
        // Populate Fields
        $('#modalRescheduleCustomerName').val(customer);
        $('#modalRescheduleDate').val(date);
        $('#modalRescheduleTime').val(time);
        $('#modalRescheduleAssignee').val(exec);
        $('#modalRescheduleTechnician').val(tech);
        $('#modalRescheduleNotes').val(notes);
        
        const rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleAppointmentModal'));
        rescheduleModal.show();
    });

    $('#rescheduleForm').on('submit', function(e) {
        if ($('#sendWaToEmployeeReschedule').is(':checked')) {
            const assigneeOption = $('#modalRescheduleAssignee option:selected');
            const empPhone = assigneeOption.data('phone');
            const empName = assigneeOption.text();
            
            if (empPhone && assigneeOption.val()) {
                let cleanPhone = empPhone.toString().replace(/[^0-9]/g, '');
                if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
                
                const customerName = $('#modalRescheduleCustomerName').val();
                const appDate = $('#modalRescheduleDate').val();
                const appTime = $('#modalRescheduleTime').val();
                const notes = $('#modalRescheduleNotes').val();
                
                const msg = `*APPOINTMENT RESCHEDULED*\n----------------------------------\nHi *${empName}*,\n\nA customer appointment has been rescheduled.\n\n👤 *Customer:* ${customerName}\n📅 *New Date:* ${appDate}\n⏰ *New Time:* ${appTime}\n📝 *Notes:* ${notes}\n\nPlease check the CRM admin panel for full details.\n\n*Metric Qube Energy Pvt. Ltd.*`;
                
                window.open('https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(msg), '_blank');
            }
        }
    });
});
</script>
@endpush
