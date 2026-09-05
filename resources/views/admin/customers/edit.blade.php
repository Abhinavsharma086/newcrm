@extends('layouts.app')

@section('title', request('section') === 'technical' ? 'Edit LMC & Technical Details' : 'Edit Customer Registration')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.customers.index', ['section' => request('section', 'registration')]) }}">Customers</a></li>
<li class="breadcrumb-item active">{{ request('section') === 'technical' ? 'Edit Technical' : 'Edit Registration' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ request('section') === 'technical' ? 'Edit LMC & Technical Details' : 'Edit Customer Registration Details' }}</h5>
            <span class="badge bg-secondary">CRN: {{ $customer->crn_no ?? 'N/A' }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customers.update', [$customer, 'section' => request('section', 'registration')]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @if(request('section') === 'technical')
                    <!-- Hidden Required Fields for Controller Validation -->
                    <input type="hidden" name="name" value="{{ $customer->name }}">
                    <input type="hidden" name="phone" value="{{ $customer->phone }}">
                    <input type="hidden" name="source" value="{{ $customer->source }}">

                    <!-- Section 2: LMC & Installation -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-tools"></i> LMC & Technical Details</h6>
                    
                    @if($customer->lmc_id)
                    <div class="mb-3">
                        <span class="badge bg-dark px-3 py-2 fs-6"><i class="fas fa-tag"></i> LMC ID: {{ $customer->lmc_id }}</span>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="lmc_date" class="form-label">LMC Date</label>
                            <input type="date" class="form-control @error('lmc_date') is-invalid @enderror" 
                                   id="lmc_date" name="lmc_date" value="{{ old('lmc_date', $customer->lmc_date ? $customer->lmc_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="burner_type" class="form-label">Burner Type</label>
                            <select class="form-select @error('burner_type') is-invalid @enderror" id="burner_type" name="burner_type">
                                <option value="">-- Select Burner Type --</option>
                                @foreach($burnerTypes as $bt)
                                    <option value="{{ $bt->name }}" {{ old('burner_type', $customer->burner_type) == $bt->name ? 'selected' : '' }}>{{ $bt->name }}</option>
                                @endforeach
                            </select>
                            @error('burner_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meter_no" class="form-label">Meter No.</label>
                            <input type="text" class="form-control @error('meter_no') is-invalid @enderror" 
                                   id="meter_no" name="meter_no" value="{{ old('meter_no', $customer->meter_no) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meter_type" class="form-label">Meter Type</label>
                            <select class="form-select @error('meter_type') is-invalid @enderror" id="meter_type" name="meter_type">
                                <option value="">-- Select Meter Type --</option>
                                @foreach($meterTypes as $mt)
                                    <option value="{{ $mt->name }}" {{ old('meter_type', $customer->meter_type) == $mt->name ? 'selected' : '' }}>{{ $mt->name }}</option>
                                @endforeach
                            </select>
                            @error('meter_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" 
                                   id="manufacturer" name="manufacturer" value="{{ old('manufacturer', $customer->manufacturer) }}">
                        </div>
                    </div>

                    <!-- Section 3: Contractor & Connection Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-file-contract"></i> Contractor & Connection Details</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="contractor_id" class="form-label">Contractor</label>
                            <select class="form-select @error('contractor_id') is-invalid @enderror" id="contractor_id" name="contractor_id">
                                <option value="">-- Select Contractor --</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('contractor_id', $customer->contractor_id) == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                            @error('contractor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="rfc_date" class="form-label">RFC Date</label>
                            <input type="date" class="form-control @error('rfc_date') is-invalid @enderror" 
                                   id="rfc_date" name="rfc_date" value="{{ old('rfc_date', $customer->rfc_date ? $customer->rfc_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="jmr_date" class="form-label">JMR Date</label>
                            <input type="date" class="form-control @error('jmr_date') is-invalid @enderror" 
                                   id="jmr_date" name="jmr_date" value="{{ old('jmr_date', $customer->jmr_date ? $customer->jmr_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="conversion_date" class="form-label">Conversion Date</label>
                            <input type="date" class="form-control @error('conversion_date') is-invalid @enderror" 
                                   id="conversion_date" name="conversion_date" value="{{ old('conversion_date', $customer->conversion_date ? $customer->conversion_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="mlc_pipe_length" class="form-label">MLC Pipe Length (Mtr)</label>
                            <input type="number" step="0.01" class="form-control @error('mlc_pipe_length') is-invalid @enderror" 
                                   id="mlc_pipe_length" name="mlc_pipe_length" value="{{ old('mlc_pipe_length', $customer->mlc_pipe_length) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="extra_mlc_amount" class="form-label">Extra MLC Amount (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('extra_mlc_amount') is-invalid @enderror" 
                                   id="extra_mlc_amount" name="extra_mlc_amount" value="{{ old('extra_mlc_amount', $customer->extra_mlc_amount) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="customer_stage" class="form-label">Stage Override / Current Stage</label>
                            <select class="form-select @error('customer_stage') is-invalid @enderror" id="customer_stage" name="customer_stage">
                                <option value="">-- Auto-calculate stage --</option>
                                <option value="New" {{ old('customer_stage', $customer->customer_stage) == 'New' ? 'selected' : '' }}>New</option>
                                <option value="Registered" {{ old('customer_stage', $customer->customer_stage) == 'Registered' ? 'selected' : '' }}>Registered</option>
                                <option value="LMC Done" {{ old('customer_stage', $customer->customer_stage) == 'LMC Done' ? 'selected' : '' }}>LMC Done</option>
                                <option value="RFC Done" {{ old('customer_stage', $customer->customer_stage) == 'RFC Done' ? 'selected' : '' }}>RFC Done</option>
                                <option value="JMR Done" {{ old('customer_stage', $customer->customer_stage) == 'JMR Done' ? 'selected' : '' }}>JMR Done</option>
                                <option value="Converted" {{ old('customer_stage', $customer->customer_stage) == 'Converted' ? 'selected' : '' }}>Converted</option>
                            </select>
                        </div>
                    </div>

                    <!-- We removed MLC section from here -->

                    <!-- Section 4: Attachments -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-paperclip"></i> Upload Documents & Installation Photos</h6>
                    <div class="row">
                        <!-- Job Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <label for="job_card" class="form-label fw-bold">Job Card File (PDF/Image)</label>
                                    <input type="file" class="form-control @error('job_card') is-invalid @enderror" id="job_card" name="job_card">
                                    @if($customer->job_card)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Job Card already uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Main Photo -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <label for="photo" class="form-label fw-bold">Customer/Meter Photo</label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo">
                                    @if($customer->photo)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Photo already uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Inside Kitchen Photo -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary">
                                <div class="card-body">
                                    <label for="inside_kitchen_photo" class="form-label fw-bold">Inside Kitchen Photo</label>
                                    <input type="file" class="form-control" id="inside_kitchen_photo" name="inside_kitchen_photo">
                                    @if($customer->inside_kitchen_photo)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Photo uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Meter Photo -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary">
                                <div class="card-body">
                                    <label for="meter_photo_3_angles" class="form-label fw-bold">Meter Photo (3 Angles)</label>
                                    <input type="file" class="form-control" id="meter_photo_3_angles" name="meter_photo_3_angles">
                                    @if($customer->meter_photo_3_angles)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Photo uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Outside Kitchen Photo -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary">
                                <div class="card-body">
                                    <label for="outside_kitchen_photo" class="form-label fw-bold">Outside Kitchen Photo</label>
                                    <input type="file" class="form-control" id="outside_kitchen_photo" name="outside_kitchen_photo">
                                    @if($customer->outside_kitchen_photo)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Photo uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- RFC Report -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-success">
                                <div class="card-body">
                                    <label for="rfc_report_image" class="form-label fw-bold">RFC Report Image</label>
                                    <input type="file" class="form-control" id="rfc_report_image" name="rfc_report_image">
                                    @if($customer->rfc_report_image)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Report image uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- JMR Report -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-success">
                                <div class="card-body">
                                    <label for="jmr_report_image" class="form-label fw-bold">JMR Report Image</label>
                                    <input type="file" class="form-control" id="jmr_report_image" name="jmr_report_image">
                                    @if($customer->jmr_report_image)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Report image uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(request('section') === 'mlc')
                    <!-- Section: MLC (Meter) Details -->
                    <h6 class="text-success mb-3 mt-4 border-bottom pb-2"><i class="fas fa-tachometer-alt"></i> MLC (Meter Line Connection) Details</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="mlc_pipe_no" class="form-label">MLC Pipe No.</label>
                            <input type="text" class="form-control @error('mlc_pipe_no') is-invalid @enderror"
                                   id="mlc_pipe_no" name="mlc_pipe_no" value="{{ old('mlc_pipe_no', $customer->mlc_pipe_no) }}" placeholder="Enter Pipe No.">
                            @error('mlc_pipe_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="male_union" class="form-label">Male Union</label>
                            <input type="text" class="form-control @error('male_union') is-invalid @enderror"
                                   id="male_union" name="male_union" value="{{ old('male_union', $customer->male_union) }}" placeholder="Male Union Details">
                            @error('male_union')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="female_union" class="form-label">Female Union</label>
                            <input type="text" class="form-control @error('female_union') is-invalid @enderror"
                                   id="female_union" name="female_union" value="{{ old('female_union', $customer->female_union) }}" placeholder="Female Union Details">
                            @error('female_union')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="isolation_valve" class="form-label">Isolation Valve / Wall</label>
                            <input type="text" class="form-control @error('isolation_valve') is-invalid @enderror"
                                   id="isolation_valve" name="isolation_valve" value="{{ old('isolation_valve', $customer->isolation_valve) }}" placeholder="Isolation Valve Details">
                            @error('isolation_valve')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="lmc_contractor_id" class="form-label">LMC Contractor</label>
                            <select class="form-select @error('lmc_contractor_id') is-invalid @enderror" id="lmc_contractor_id" name="lmc_contractor_id">
                                <option value="">-- Select LMC Contractor --</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('lmc_contractor_id', $customer->lmc_contractor_id) == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                            @error('lmc_contractor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @else
                    <!-- Section 1: Customer Registration Details -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-user-check"></i> Customer Registration Details</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="gstin" class="form-label">GST Number (Optional)</label>
                            <div class="position-relative">
                                <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                       id="gstin" name="gstin" value="{{ old('gstin', $customer->gstin) }}" placeholder="15 char GSTIN" maxlength="15">
                                <div id="gstSpinner" class="spinner-border spinner-border-sm text-primary position-absolute d-none" role="status" style="right: 10px; top: 10px;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            @error('gstin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="crn_no" class="form-label">CRN Number</label>
                            <input type="text" class="form-control @error('crn_no') is-invalid @enderror" 
                                   id="crn_no" name="crn_no" value="{{ old('crn_no', $customer->crn_no) }}">
                            @error('crn_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sap_bp_id" class="form-label">SAP BP ID</label>
                            <input type="text" class="form-control @error('sap_bp_id') is-invalid @enderror" 
                                   id="sap_bp_id" name="sap_bp_id" value="{{ old('sap_bp_id', $customer->sap_bp_id) }}">
                            @error('sap_bp_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="society" class="form-label">Society</label>
                            <select class="form-select @error('society') is-invalid @enderror" id="society" name="society">
                                <option value="">-- Select Society --</option>
                                @foreach($societies as $society)
                                    <option value="{{ $society->name }}" {{ old('society', $customer->society) == $society->name ? 'selected' : '' }}>
                                        {{ $society->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('society')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="phone" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                            <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                                <option value="manual" {{ old('source', $customer->source) == 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="whatsapp" {{ old('source', $customer->source) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="web" {{ old('source', $customer->source) == 'web' ? 'selected' : '' }}>Website</option>
                            </select>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">Assigned Employee</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                <option value="">-- Unassigned --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('assigned_to', $customer->assigned_to) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="1">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $customer->city) }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $customer->state) }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="pin" class="form-label">PIN</label>
                            <input type="text" class="form-control @error('pin') is-invalid @enderror" id="pin" name="pin" value="{{ old('pin', $customer->pin) }}">
                        </div>
                    </div>

                    <!-- KYC Documents -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-id-card"></i> KYC Documents</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="primary_id_number" class="form-label">Aadhaar Card Number</label>
                            <input type="text" class="form-control @error('primary_id_number') is-invalid @enderror" 
                                   id="primary_id_number" name="primary_id_number" value="{{ old('primary_id_number', $customer->primary_id_number) }}" placeholder="12-digit Aadhaar Number">
                            @error('primary_id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="primary_id_file" class="form-label">Aadhaar Card Photo/PDF</label>
                            <input type="file" class="form-control @error('primary_id_file') is-invalid @enderror" id="primary_id_file" name="primary_id_file">
                            @if($customer->primary_id_file)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $customer->primary_id_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fas fa-eye"></i> View Current
                                    </a>
                                </div>
                            @endif
                            @error('primary_id_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="secondary_id_type" class="form-label">Secondary ID Type</label>
                            <select class="form-select @error('secondary_id_type') is-invalid @enderror" id="secondary_id_type" name="secondary_id_type">
                                <option value="">-- Select Type --</option>
                                <option value="PAN Card" {{ old('secondary_id_type', $customer->secondary_id_type) == 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                                <option value="Voter ID" {{ old('secondary_id_type', $customer->secondary_id_type) == 'Voter ID' ? 'selected' : '' }}>Voter ID</option>
                                <option value="Passport" {{ old('secondary_id_type', $customer->secondary_id_type) == 'Passport' ? 'selected' : '' }}>Passport</option>
                                <option value="Driving License" {{ old('secondary_id_type', $customer->secondary_id_type) == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                            </select>
                            @error('secondary_id_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="secondary_id_number" class="form-label">Secondary ID Number</label>
                            <input type="text" class="form-control @error('secondary_id_number') is-invalid @enderror" 
                                   id="secondary_id_number" name="secondary_id_number" value="{{ old('secondary_id_number', $customer->secondary_id_number) }}">
                            @error('secondary_id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="secondary_id_file" class="form-label">Secondary ID Photo/PDF</label>
                            <input type="file" class="form-control @error('secondary_id_file') is-invalid @enderror" id="secondary_id_file" name="secondary_id_file">
                            @if($customer->secondary_id_file)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $customer->secondary_id_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fas fa-eye"></i> View Current
                                    </a>
                                </div>
                            @endif
                            @error('secondary_id_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="passport_photo" class="form-label">Passport Size Photo (Image)</label>
                            <input type="file" class="form-control @error('passport_photo') is-invalid @enderror" id="passport_photo" name="passport_photo">
                            @if($customer->passport_photo)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $customer->passport_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fas fa-eye"></i> View Current
                                    </a>
                                </div>
                            @endif
                            @error('passport_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="address_proof_file" class="form-label">Address Proof (Image/PDF)</label>
                            <input type="file" class="form-control @error('address_proof_file') is-invalid @enderror" id="address_proof_file" name="address_proof_file">
                            @if($customer->address_proof_file)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $customer->address_proof_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fas fa-eye"></i> View Current
                                    </a>
                                </div>
                            @endif
                            @error('address_proof_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Details & Dates -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-hand-holding-usd"></i> Payment & Registration Details</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="registration_date" class="form-label">Registration Date</label>
                            <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                   id="registration_date" name="registration_date" value="{{ old('registration_date', $customer->registration_date ? $customer->registration_date->format('Y-m-d') : '') }}">
                            @error('registration_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="reg_amount" class="form-label">Reg. Amount (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('reg_amount') is-invalid @enderror" 
                                   id="reg_amount" name="reg_amount" value="{{ old('reg_amount', $customer->reg_amount) }}">
                            @error('reg_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mode_of_payment" class="form-label">Mode of Payment</label>
                            <input type="text" class="form-control @error('mode_of_payment') is-invalid @enderror" 
                                   id="mode_of_payment" name="mode_of_payment" value="{{ old('mode_of_payment', $customer->mode_of_payment) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_ref_no" class="form-label">Cheque/Ref No.</label>
                            <input type="text" class="form-control @error('payment_ref_no') is-invalid @enderror" 
                                   id="payment_ref_no" name="payment_ref_no" value="{{ old('payment_ref_no', $customer->payment_ref_no) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', $customer->payment_date ? $customer->payment_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                @endif

                <div class="mb-3 mt-4">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks', $customer->remarks) }}</textarea>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Details
                    </button>
                    <a href="{{ route('admin.customers.index', ['section' => request('section', 'registration')]) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// GST Verification API trigger (only runs if element exists in DOM)
const gstinInput = document.getElementById('gstin');
if (gstinInput) {
    gstinInput.addEventListener('input', function() {
        const gstin = this.value.toUpperCase();
        this.value = gstin;
        
        if (gstin.length === 15) {
            const spinner = document.getElementById('gstSpinner');
            spinner.classList.remove('d-none');
            
            fetch(`/admin/api/verify-gstin/${gstin}`)
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('d-none');
                    if (data.success && data.data) {
                        const name = data.data.name || data.data.legal_name || data.data.trade_name;
                        if (document.getElementById('name').value === '') {
                            document.getElementById('name').value = name;
                        }
                        if (document.getElementById('address').value === '') {
                            document.getElementById('address').value = data.data.address || '';
                        }
                        if (document.getElementById('city').value === '' || document.getElementById('city').value === 'Delhi') {
                            document.getElementById('city').value = data.data.city || '';
                        }
                        if (document.getElementById('state').value === '' || document.getElementById('state').value === 'Delhi') {
                            document.getElementById('state').value = data.data.state || '';
                        }
                        if (document.getElementById('pin').value === '') {
                            document.getElementById('pin').value = data.data.zip || '';
                        }
                    }
                })
                .catch(error => {
                    spinner.classList.add('d-none');
                    console.error('Error fetching GST details:', error);
                });
        }
    });
}
</script>
@endpush
