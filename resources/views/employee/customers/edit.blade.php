@extends('layouts.app')

@section('title', request('section') === 'registration' ? 'Upload KYC Documents' : 'Update LMC Data')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('employee.customers.index', ['section' => request('section', 'registration')]) }}">Customers</a></li>
<li class="breadcrumb-item active">{{ request('section') === 'registration' ? 'Upload KYC' : 'Update LMC' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ request('section') === 'registration' ? 'Collect & Upload KYC Documents' : 'Update Technical & LMC Data' }} for: <strong>{{ $customer->name }}</strong></h5>
            <p class="text-muted mb-0">CRN: <code>{{ $customer->crn_no ?? 'N/A' }}</code> | Society: {{ $customer->society ?? 'N/A' }}</p>
        </div>
        <div class="card-body">
            <form action="{{ route('employee.customers.update', [$customer, 'section' => request('section', 'registration')]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if(request('section') === 'registration')
                    <!-- Section 4: KYC Documents -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-id-card"></i> KYC Documents</h6>
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
                @else
                    <!-- Section 1: LMC Details -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-tools"></i> LMC & Meter Specifications</h6>
                    
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
                            @error('lmc_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="burner_type" class="form-label">Burner Type</label>
                            <input type="text" class="form-control @error('burner_type') is-invalid @enderror" 
                                   id="burner_type" name="burner_type" value="{{ old('burner_type', $customer->burner_type) }}">
                            @error('burner_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meter_no" class="form-label">Meter No.</label>
                            <input type="text" class="form-control @error('meter_no') is-invalid @enderror" 
                                   id="meter_no" name="meter_no" value="{{ old('meter_no', $customer->meter_no) }}">
                            @error('meter_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meter_type" class="form-label">Meter Type</label>
                            <input type="text" class="form-control @error('meter_type') is-invalid @enderror" 
                                   id="meter_type" name="meter_type" value="{{ old('meter_type', $customer->meter_type) }}">
                            @error('meter_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" 
                                   id="manufacturer" name="manufacturer" value="{{ old('manufacturer', $customer->manufacturer) }}">
                            @error('manufacturer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Section 2: Contractors -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-network-wired"></i> RFC & JMR Contractor Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rfc_contractor" class="form-label">RFC Contractor</label>
                            <input type="text" class="form-control @error('rfc_contractor') is-invalid @enderror" 
                                   id="rfc_contractor" name="rfc_contractor" value="{{ old('rfc_contractor', $customer->rfc_contractor) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfc_date" class="form-label">RFC Date</label>
                            <input type="date" class="form-control @error('rfc_date') is-invalid @enderror" 
                                   id="rfc_date" name="rfc_date" value="{{ old('rfc_date', $customer->rfc_date ? $customer->rfc_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jmr_contractor" class="form-label">JMR Contractor</label>
                            <input type="text" class="form-control @error('jmr_contractor') is-invalid @enderror" 
                                   id="jmr_contractor" name="jmr_contractor" value="{{ old('jmr_contractor', $customer->jmr_contractor) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jmr_date" class="form-label">JMR Date</label>
                            <input type="date" class="form-control @error('jmr_date') is-invalid @enderror" 
                                   id="jmr_date" name="jmr_date" value="{{ old('jmr_date', $customer->jmr_date ? $customer->jmr_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <!-- We removed MLC section from here -->

                    <!-- Section 3: Uploads & Comments -->
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

                        <!-- Inside Kitchen Photo -->
                        <div class="col-md-6 mb-4">
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
                    </div>

                    <div class="row">
                        <!-- Meter Photo -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary">
                                <div class="card-body">
                                    <label for="meter_photo_3_angles" class="form-label fw-bold">Meter Photo</label>
                                    <input type="file" class="form-control" id="meter_photo_3_angles" name="meter_photo_3_angles">
                                    @if($customer->meter_photo_3_angles)
                                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Photo uploaded.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Outside Kitchen Photo -->
                        <div class="col-md-6 mb-4">
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
                                @foreach(\App\Models\Contractor::where('is_active', true)->orderBy('name')->get() as $contractor)
                                    <option value="{{ $contractor->id }}" {{ old('lmc_contractor_id', $customer->lmc_contractor_id) == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                            @error('lmc_contractor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $customer->remarks) }}</textarea>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Details
                    </button>
                    <a href="{{ route('employee.customers.index', ['section' => request('section', 'registration')]) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
