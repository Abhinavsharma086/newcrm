@extends('layouts.app')

@section('title', 'My Appointments')

@section('breadcrumb')
<li class="breadcrumb-item active">My Appointments</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-alt text-primary me-2"></i>My Appointments Schedule</h2>
    </div>

    <x-card>
        <table id="empAppointmentsTable" class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Society</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $app)
                <tr>
                    <td>
                        <strong>{{ $app->customer?->name ?? 'N/A' }}</strong>
                        <br><small class="text-muted">{{ $app->customer?->phone }}</small>
                    </td>
                    <td>{{ $app->customer?->society ?? 'N/A' }}</td>
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
                        <span class="badge bg-{{ $app->status_badge_color }}">
                            {{ ucfirst($app->appointment_status) }}
                        </span>
                    </td>
                    <td>{{ Str::limit($app->notes, 30) }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#responseModal-{{ $app->id }}">
                            <i class="fas fa-reply"></i> Respond
                        </button>

                        <!-- Response Modal -->
                        <div class="modal fade" id="responseModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('employee.appointments.response', $app) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="fas fa-reply"></i> Submit Response</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <p class="text-muted mb-3">Customer: <strong>{{ $app->customer?->name }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Update Status <span class="text-danger">*</span></label>
                                                <select name="appointment_status" class="form-select" required>
                                                    <option value="confirmed" {{ $app->appointment_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="visited" {{ $app->appointment_status == 'visited' ? 'selected' : '' }}>Visited</option>
                                                    <option value="converted" {{ $app->appointment_status == 'converted' ? 'selected' : '' }}>Converted</option>
                                                    <option value="denied" {{ $app->appointment_status == 'denied' ? 'selected' : '' }}>Denied</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Notes</label>
                                                <textarea name="notes" class="form-control" rows="3" placeholder="Add field notes...">{{ $app->notes }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Follow-Up Date</label>
                                                <input type="date" name="follow_up_date" class="form-control" value="{{ $app->follow_up_date ? $app->follow_up_date->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Follow-Up Note</label>
                                                <textarea name="follow_up_note" class="form-control" rows="2" placeholder="Follow-up details...">{{ $app->follow_up_note }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Submit Response</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                        No appointments assigned to you yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#empAppointmentsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'print'],
        order: [[2, 'desc']]
    });
});
</script>
@endpush
