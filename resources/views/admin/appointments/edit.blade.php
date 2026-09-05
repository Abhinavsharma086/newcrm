@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
<li class="breadcrumb-item active">Edit Appointment</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Appointment details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $appointment->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="burner_type" class="form-label">Burner Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('burner_type') is-invalid @enderror" id="burner_type" name="burner_type" required>
                            <option value="Normal" {{ old('burner_type', $appointment->burner_type) == 'Normal' ? 'selected' : '' }}>Normal</option>
                            <option value="Hob Stove" {{ old('burner_type', $appointment->burner_type) == 'Hob Stove' ? 'selected' : '' }}>Hob Stove</option>
                        </select>
                        @error('burner_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="assigned_to" class="form-label">Assign Exec (Marketing)</label>
                        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('assigned_to', $appointment->assigned_to) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="assigned_technician" class="form-label">Assign Tech (Field Tech)</label>
                        <select class="form-select @error('assigned_technician') is-invalid @enderror" id="assigned_technician" name="assigned_technician">
                            <option value="">Unassigned</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('assigned_technician', $appointment->assigned_technician) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_technician')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Appointment Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $appointment->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="appointment_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                               id="appointment_date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : '') }}" required>
                        @error('appointment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="appointment_time" class="form-label">Time Slot <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                               id="appointment_time" name="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time) }}" required>
                        @error('appointment_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="appointment_status" class="form-label">Appointment Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('appointment_status') is-invalid @enderror" id="appointment_status" name="appointment_status" required>
                            <option value="scheduled" {{ old('appointment_status', $appointment->appointment_status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="confirmed" {{ old('appointment_status', $appointment->appointment_status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="visited" {{ old('appointment_status', $appointment->appointment_status) == 'visited' ? 'selected' : '' }}>Visited</option>
                            <option value="rescheduled" {{ old('appointment_status', $appointment->appointment_status) == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            <option value="converted" {{ old('appointment_status', $appointment->appointment_status) == 'converted' ? 'selected' : '' }}>Converted (Success)</option>
                            <option value="denied" {{ old('appointment_status', $appointment->appointment_status) == 'denied' ? 'selected' : '' }}>Denied</option>
                        </select>
                        @error('appointment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('source') is-invalid @enderror" 
                               id="source" name="source" value="{{ old('source', $appointment->source) }}" required>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="follow_up_date" class="form-label">Follow Up Date</label>
                        <input type="date" class="form-control @error('follow_up_date') is-invalid @enderror" 
                               id="follow_up_date" name="follow_up_date" value="{{ old('follow_up_date', $appointment->follow_up_date ? $appointment->follow_up_date->format('Y-m-d') : '') }}">
                        @error('follow_up_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kitchen_burner_video" class="form-label fw-bold">1. Kitchen & Burner Video (MP4/MOV)</label>
                        <input type="file" class="form-control" id="kitchen_burner_video" name="kitchen_burner_video" accept="video/*">
                        @if($appointment->kitchen_burner_video)
                            <div class="mt-2">
                                <span class="badge bg-success"><i class="fas fa-video me-1"></i> Video Uploaded</span>
                                <a href="{{ asset('storage/' . $appointment->kitchen_burner_video) }}" target="_blank" class="small ms-2">Play Video</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="external_riser_video" class="form-label fw-bold">2. External Riser Pipe & Tapping Point Video (MP4/MOV)</label>
                        <input type="file" class="form-control" id="external_riser_video" name="external_riser_video" accept="video/*">
                        @if($appointment->external_riser_video)
                            <div class="mt-2">
                                <span class="badge bg-success"><i class="fas fa-video me-1"></i> Video Uploaded</span>
                                <a href="{{ asset('storage/' . $appointment->external_riser_video) }}" target="_blank" class="small ms-2">Play Video</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="follow_up_note" class="form-label">Follow Up Remark</label>
                    <textarea class="form-control @error('follow_up_note') is-invalid @enderror" id="follow_up_note" name="follow_up_note" rows="2" placeholder="Next step actions...">{{ old('follow_up_note', $appointment->follow_up_note) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="General description...">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Appointment
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
