@extends('layouts.app')

@section('title', 'Appointment Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
<li class="breadcrumb-item active">Appointment Details</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Appointment Profile</h2>
        <div>
            @if($appointment->isDenied())
                <form action="{{ route('admin.appointments.reopen', $appointment) }}" method="POST" class="d-inline-block">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo"></i> Reopen Appointment
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Appointment
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <x-card class="mb-4">
                <h5 class="text-primary mb-3"><i class="fas fa-calendar-check"></i> Appointment Information</h5>
                <table class="table table-borderless table-sm">
                    <tr><th style="width: 30%;">Title:</th><td><strong>{{ $appointment->title }}</strong></td></tr>
                    <tr><th>Customer Name:</th><td>{{ $appointment->customer?->name ?? 'N/A' }}</td></tr>
                    <tr><th>Society:</th><td>{{ $appointment->customer?->society ?? 'N/A' }}</td></tr>
                    <tr><th>Contact Phone:</th><td>{{ $appointment->customer?->phone ?? 'N/A' }}</td></tr>
                    <tr>
                        <th>Appointment Date:</th>
                        <td>
                            <span class="badge bg-primary fs-6">
                                <i class="fas fa-calendar-alt"></i> 
                                {{ $appointment->appointment_date ? $appointment->appointment_date->format('d M Y') : 'Not Set' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Scheduled Time Slot:</th>
                        <td>
                            <span class="badge bg-secondary fs-6">
                                <i class="fas fa-clock"></i> 
                                {{ $appointment->appointment_time ?? 'Not Set' }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Assigned Exec (Marketing):</th><td>{{ $appointment->assignee?->name ?? 'Unassigned' }}</td></tr>
                    <tr><th>Field Technician:</th><td>{{ $appointment->technician?->name ?? 'Unassigned' }}</td></tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $appointment->status_badge_color }} fs-6">
                                {{ ucfirst($appointment->appointment_status) }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Burner Type:</th><td><span class="badge bg-dark">{{ $appointment->burner_type }}</span></td></tr>
                    <tr>
                        <th>Kitchen & Burner Video:</th>
                        <td>
                            @if($appointment->kitchen_burner_video)
                                <a href="{{ asset('storage/' . $appointment->kitchen_burner_video) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2">
                                    <i class="fas fa-play-circle me-1"></i> Play Video
                                </a>
                            @else
                                <span class="text-muted">No video uploaded</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>External Riser Video:</th>
                        <td>
                            @if($appointment->external_riser_video)
                                <a href="{{ asset('storage/' . $appointment->external_riser_video) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2">
                                    <i class="fas fa-play-circle me-1"></i> Play Video
                                </a>
                            @else
                                <span class="text-muted">No video uploaded</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Source:</th><td><span class="badge bg-info">{{ $appointment->source }}</span></td></tr>
                </table>
            </x-card>

            @if($appointment->isDenied())
            <x-card class="mb-4 border-danger">
                <h5 class="text-danger mb-3"><i class="fas fa-ban"></i> Denial Case Log</h5>
                <table class="table table-bordered table-sm align-middle text-dark">
                    <tr><th style="width: 30%;">Person Who Denied:</th><td><strong>{{ $appointment->denied_by_person }}</strong></td></tr>
                    <tr><th>Reason:</th><td class="text-danger">{{ $appointment->denial_reason }}</td></tr>
                    <tr><th>Further Action:</th><td><span class="badge bg-warning text-dark">{{ ucfirst($appointment->further_action) }}</span></td></tr>
                    @if($appointment->further_action_date)
                        <tr><th>Action Date:</th><td>{{ $appointment->further_action_date->format('d M Y') }}</td></tr>
                    @endif
                    @if($appointment->denial_photo)
                        <tr>
                            <th>Photograph of Visit:</th>
                            <td>
                                <a href="{{ asset('storage/' . $appointment->denial_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $appointment->denial_photo) }}" class="img-thumbnail" style="max-height: 150px;" alt="Visit photo">
                                </a>
                            </td>
                        </tr>
                    @endif
                </table>
            </x-card>
            @endif

            @if($appointment->notes)
            <x-card class="mb-4">
                <h5 class="text-primary mb-2"><i class="fas fa-sticky-note"></i> Internal Notes</h5>
                <p class="mb-0 text-secondary bg-light p-3 rounded border">{{ $appointment->notes }}</p>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
