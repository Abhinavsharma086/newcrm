@extends('layouts.app')

@section('title', 'Add Customer')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">Add Customer</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Customer</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Section 1: Customer Registration Details -->
                <h6 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-user-check"></i> Customer Registration Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="gstin" class="form-label">GST Number (Optional)</label>
                        <div class="position-relative">
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                   id="gstin" name="gstin" value="{{ old('gstin') }}" placeholder="15 char GSTIN" maxlength="15">
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
                               id="crn_no" name="crn_no" value="{{ old('crn_no') }}">
                        @error('crn_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sap_bp_id" class="form-label">SAP BP ID</label>
                        <input type="text" class="form-control @error('sap_bp_id') is-invalid @enderror" 
                               id="sap_bp_id" name="sap_bp_id" value="{{ old('sap_bp_id') }}">
                        @error('sap_bp_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="name" class="form-label">Customer/Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
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
                                <option value="{{ $society->name }}" {{ old('society') == $society->name ? 'selected' : '' }}>
                                    {{ $society->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('society')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                        <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                            <option value="manual" {{ old('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="whatsapp" {{ old('source') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="web" {{ old('source') == 'web' ? 'selected' : '' }}>Website</option>
                        </select>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="assigned_to" class="form-label">Assigned Employee</label>
                        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('assigned_to') == $employee->id ? 'selected' : '' }}>
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
                                  id="address" name="address" rows="1">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="pin" class="form-label">PIN</label>
                        <input type="text" class="form-control @error('pin') is-invalid @enderror" id="pin" name="pin" value="{{ old('pin') }}">
                    </div>
                </div>

                <!-- KYC Documents -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="primary_id_number" class="form-label">Aadhaar Card Number</label>
                        <input type="text" class="form-control @error('primary_id_number') is-invalid @enderror" 
                               id="primary_id_number" name="primary_id_number" value="{{ old('primary_id_number') }}" placeholder="12-digit Aadhaar Number">
                        @error('primary_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="primary_id_file" class="form-label">Aadhaar Card Photo/PDF</label>
                        <input type="file" class="form-control @error('primary_id_file') is-invalid @enderror" id="primary_id_file" name="primary_id_file">
                        @error('primary_id_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="secondary_id_type" class="form-label">Secondary ID Type</label>
                        <select class="form-select @error('secondary_id_type') is-invalid @enderror" id="secondary_id_type" name="secondary_id_type">
                            <option value="">-- Select Type --</option>
                            <option value="PAN Card" {{ old('secondary_id_type') == 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                            <option value="Voter ID" {{ old('secondary_id_type') == 'Voter ID' ? 'selected' : '' }}>Voter ID</option>
                            <option value="Passport" {{ old('secondary_id_type') == 'Passport' ? 'selected' : '' }}>Passport</option>
                            <option value="Driving License" {{ old('secondary_id_type') == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                        </select>
                        @error('secondary_id_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="secondary_id_number" class="form-label">Secondary ID Number</label>
                        <input type="text" class="form-control @error('secondary_id_number') is-invalid @enderror" 
                               id="secondary_id_number" name="secondary_id_number" value="{{ old('secondary_id_number') }}">
                        @error('secondary_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="secondary_id_file" class="form-label">Secondary ID Photo/PDF</label>
                        <input type="file" class="form-control @error('secondary_id_file') is-invalid @enderror" id="secondary_id_file" name="secondary_id_file">
                        @error('secondary_id_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="passport_photo" class="form-label">Passport Size Photo (Image)</label>
                        <input type="file" class="form-control @error('passport_photo') is-invalid @enderror" id="passport_photo" name="passport_photo">
                        @error('passport_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="address_proof_file" class="form-label">Address Proof (Image/PDF)</label>
                        <input type="file" class="form-control @error('address_proof_file') is-invalid @enderror" id="address_proof_file" name="address_proof_file">
                        @error('address_proof_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="mode_of_payment" class="form-label">Mode of Payment</label>
                        <input type="text" class="form-control @error('mode_of_payment') is-invalid @enderror" 
                               id="mode_of_payment" name="mode_of_payment" value="{{ old('mode_of_payment') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="payment_ref_no" class="form-label">Cheque/Ref No.</label>
                        <input type="text" class="form-control @error('payment_ref_no') is-invalid @enderror" 
                               id="payment_ref_no" name="payment_ref_no" value="{{ old('payment_ref_no') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                               id="payment_date" name="payment_date" value="{{ old('payment_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="reg_amount" class="form-label">Reg. Amount (₹)</label>
                        <input type="number" step="0.01" class="form-control @error('reg_amount') is-invalid @enderror" 
                               id="reg_amount" name="reg_amount" value="{{ old('reg_amount') }}">
                    </div>
                </div>

                <!-- Section 4: Attachments -->
                <h6 class="text-primary mb-3 mt-4 border-bottom pb-2"><i class="fas fa-paperclip"></i> Uploads & Remarks</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="job_card" class="form-label">Job Card (PDF/Image)</label>
                        <input type="file" class="form-control @error('job_card') is-invalid @enderror" id="job_card" name="job_card">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="photo" class="form-label">Photo (Image)</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo">
                    </div>
                </div>

                <!-- Site Photos Section -->
                <h6 class="text-info mb-3 mt-4 border-bottom pb-2"><i class="fas fa-camera"></i> Site Photos</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="inside_kitchen_photo" class="form-label"><i class="fas fa-utensils text-warning"></i> Inside Kitchen Photo</label>
                        <input type="file" class="form-control @error('inside_kitchen_photo') is-invalid @enderror" 
                               id="inside_kitchen_photo" name="inside_kitchen_photo" accept="image/*" onchange="previewImage(this, 'preview_kitchen')">
                        @error('inside_kitchen_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="preview_kitchen" src="#" alt="Kitchen Preview" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="outside_kitchen_photo" class="form-label"><i class="fas fa-building text-success"></i> Outside Riser Photo</label>
                        <input type="file" class="form-control @error('outside_kitchen_photo') is-invalid @enderror" 
                               id="outside_kitchen_photo" name="outside_kitchen_photo" accept="image/*" onchange="previewImage(this, 'preview_riser')">
                        @error('outside_kitchen_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="preview_riser" src="#" alt="Riser Preview" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="meter_photo_3_angles" class="form-label"><i class="fas fa-tachometer-alt text-primary"></i> Meter Photo (3 Angles)</label>
                        <input type="file" class="form-control @error('meter_photo_3_angles') is-invalid @enderror" 
                               id="meter_photo_3_angles" name="meter_photo_3_angles" accept="image/*" onchange="previewImage(this, 'preview_meter')">
                        @error('meter_photo_3_angles')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="preview_meter" src="#" alt="Meter Preview" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Customer
                    </button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.classList.add('d-none');
    }
}

document.getElementById('gstin').addEventListener('input', function() {
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
                    if (document.getElementById('name').value === '' || document.getElementById('name').value.startsWith('Demo')) {
                        document.getElementById('name').value = name;
                    }
                    if (document.getElementById('address').value === '' || document.getElementById('address').value.includes('Fake')) {
                        document.getElementById('address').value = data.data.address || '';
                    }
                    if (document.getElementById('city').value === '' || document.getElementById('city').value === 'Metropolis') {
                        document.getElementById('city').value = data.data.city || '';
                    }
                    if (document.getElementById('state').value === '') {
                        document.getElementById('state').value = data.data.state || '';
                    }
                    if (document.getElementById('pin').value === '' || document.getElementById('pin').value === '400001') {
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
</script>
@endpush
@endsection
