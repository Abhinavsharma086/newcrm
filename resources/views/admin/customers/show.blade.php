@extends('layouts.app')

@section('title', 'Customer Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.customers.index', ['section' => request('section', 'registration')]) }}">Customers</a></li>
<li class="breadcrumb-item active">Customer Details</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $customer->name }}</h2>
            <p class="text-muted mb-0">CRN Number: <code>{{ $customer->crn_no ?? 'N/A' }}</code> | Stage: <span class="badge bg-{{ $customer->stage_badge_color }}">{{ $customer->customer_stage ?? $customer->auto_stage }}</span></p>
        </div>
        <div>
            <a href="{{ route('admin.customers.edit', [$customer, 'section' => request('section', 'registration')]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="{{ route('admin.customers.index', ['section' => request('section', 'registration')]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Bootstrap Nav Tabs -->
    <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded shadow-sm" id="customerTabs" role="tablist" style="border: 1px solid #dee2e6;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active py-2 fw-medium" id="registration-tab" data-bs-toggle="tab" data-bs-target="#registration" type="button" role="tab" aria-controls="registration" aria-selected="true">
                <i class="fas fa-address-card me-2"></i> Registration & KYC Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 fw-medium" id="technical-tab" data-bs-toggle="tab" data-bs-target="#technical" type="button" role="tab" aria-controls="technical" aria-selected="false">
                <i class="fas fa-tools me-2"></i> LMC & Technical Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 fw-medium" id="mlc-tab" data-bs-toggle="tab" data-bs-target="#mlc" type="button" role="tab" aria-controls="mlc" aria-selected="false">
                <i class="fas fa-tachometer-alt me-2"></i> MLC (Meter) Details
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="customerTabsContent">
        
        <!-- Tab 1: Registration & KYC -->
        <div class="tab-pane fade show active" id="registration" role="tabpanel" aria-labelledby="registration-tab">
            <div class="row">
                <!-- Left Column: Registration Details & Payment -->
                <div class="col-md-8">
                    <!-- Customer Information Card -->
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-user-circle"></i> Customer Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 40%;" class="text-secondary">Full Name:</th><td class="fw-bold">{{ $customer->name }}</td></tr>
                                    <tr><th class="text-secondary">CRN Number:</th><td><code>{{ $customer->crn_no ?? 'N/A' }}</code></td></tr>
                                    <tr><th class="text-secondary">SAP BP ID:</th><td><code>{{ $customer->sap_bp_id ?? 'N/A' }}</code></td></tr>
                                    <tr><th class="text-secondary">Mobile No:</th><td>{{ $customer->phone }}</td></tr>
                                    <tr><th class="text-secondary">Email Addr:</th><td>{{ $customer->email ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Lead Source:</th><td><span class="badge bg-info">{{ ucfirst($customer->source) }}</span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 30%;" class="text-secondary">Society:</th><td>{{ $customer->society ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Address:</th><td>{{ $customer->address ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">City:</th><td>{{ $customer->city ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">State:</th><td>{{ $customer->state ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">PIN Code:</th><td>{{ $customer->pin ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">GSTIN:</th><td>{{ $customer->gstin ?? 'N/A' }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </x-card>

                    <!-- Payment Information Card -->
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-hand-holding-usd"></i> Payment & Registration Fees</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">Registration Fee:</th><td class="fw-bold text-success">₹{{ number_format($customer->reg_amount ?? 0, 2) }}</td></tr>
                                    <tr><th class="text-secondary">Payment Mode:</th><td><span class="badge bg-light text-dark border">{{ $customer->mode_of_payment ?? 'N/A' }}</span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">Payment Ref No:</th><td><code>{{ $customer->payment_ref_no ?? 'N/A' }}</code></td></tr>
                                    <tr><th class="text-secondary">Payment Date:</th><td>{{ $customer->payment_date ? $customer->payment_date->format('d M Y') : 'N/A' }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </x-card>

                    @if($customer->remarks)
                    <!-- Remarks Card -->
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-2"><i class="fas fa-comment-alt"></i> Administrative Remarks</h5>
                        <p class="mb-0 text-secondary bg-light p-3 rounded border" style="font-size: 14px;">{{ $customer->remarks }}</p>
                    </x-card>
                    @endif
                </div>

                <!-- Right Column: KYC Uploads & Previews -->
                <div class="col-md-4">
                    <x-card class="mb-4 h-100">
                        <h5 class="text-primary mb-3"><i class="fas fa-id-card"></i> KYC Verification Documents</h5>
                        
                        <!-- Aadhaar Card -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fas fa-id-card text-secondary me-1"></i> Aadhaar Card:</span>
                                @if($customer->primary_id_file)
                                    <span class="badge bg-success">Uploaded</span>
                                @else
                                    <span class="badge bg-secondary opacity-75">Missing</span>
                                @endif
                            </div>
                            <small class="text-muted d-block mb-2">No: {{ $customer->primary_id_number ?? 'Not Entered' }}</small>
                            @if($customer->primary_id_file)
                                <a href="{{ asset('storage/' . $customer->primary_id_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1">
                                    <i class="fas fa-eye me-1"></i> View Aadhaar File
                                </a>
                            @endif
                        </div>

                        <!-- Secondary ID Card -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fas fa-passport text-secondary me-1"></i> {{ $customer->secondary_id_type ?? 'Secondary ID' }}:</span>
                                @if($customer->secondary_id_file)
                                    <span class="badge bg-success">Uploaded</span>
                                @else
                                    <span class="badge bg-secondary opacity-75">Missing</span>
                                @endif
                            </div>
                            <small class="text-muted d-block mb-2">No: {{ $customer->secondary_id_number ?? 'Not Entered' }}</small>
                            @if($customer->secondary_id_file)
                                <a href="{{ asset('storage/' . $customer->secondary_id_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1">
                                    <i class="fas fa-eye me-1"></i> View Secondary ID
                                </a>
                            @endif
                        </div>

                        <!-- Passport Photo -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fas fa-user-circle text-secondary me-1"></i> Passport Photo:</span>
                                @if($customer->passport_photo)
                                    <span class="badge bg-success">Uploaded</span>
                                @else
                                    <span class="badge bg-secondary opacity-75">Missing</span>
                                @endif
                            </div>
                            @if($customer->passport_photo)
                                <div class="text-center my-2">
                                    <img src="{{ asset('storage/' . $customer->passport_photo) }}" class="img-thumbnail rounded" style="max-height: 100px;" alt="Passport Photo">
                                </div>
                                <a href="{{ asset('storage/' . $customer->passport_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1">
                                    <i class="fas fa-eye me-1"></i> View Full Photo
                                </a>
                            @endif
                        </div>

                        <!-- Address Proof -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fas fa-home text-secondary me-1"></i> Address Proof:</span>
                                @if($customer->address_proof_file)
                                    <span class="badge bg-success">Uploaded</span>
                                @else
                                    <span class="badge bg-secondary opacity-75">Missing</span>
                                @endif
                            </div>
                            @if($customer->address_proof_file)
                                <a href="{{ asset('storage/' . $customer->address_proof_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1">
                                    <i class="fas fa-eye me-1"></i> View Address Proof
                                </a>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        </div>

        <!-- Tab 2: LMC & Technical Details -->
        <div class="tab-pane fade" id="technical" role="tabpanel" aria-labelledby="technical-tab">
            <div class="row">
                <!-- Left Column: Technical Dates and Specifications -->
                <div class="col-md-7">
                    <!-- Technical Specs Card -->
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-compass"></i> Technical Specifications</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">LMC ID:</th><td>
                                        @if($customer->lmc_id)
                                            <span class="badge bg-dark font-monospace">{{ $customer->lmc_id }}</span>
                                        @else
                                            <span class="text-muted">Not Generated Yet</span>
                                        @endif
                                    </td></tr>
                                    <tr><th class="text-secondary">Registration Date:</th><td>{{ $customer->registration_date ? $customer->registration_date->format('d M Y') : 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">LMC Date:</th><td>{{ $customer->lmc_date ? $customer->lmc_date->format('d M Y') : 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Meter Number:</th><td><code>{{ $customer->meter_no ?? 'N/A' }}</code></td></tr>
                                    <tr><th class="text-secondary">Meter Type:</th><td>{{ $customer->meter_type ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Manufacturer:</th><td>{{ $customer->manufacturer ?? 'N/A' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">Burner Type:</th><td>{{ $customer->burner_type ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">MLC Pipe Length:</th><td>{{ $customer->mlc_pipe_length ? $customer->mlc_pipe_length . ' Mtr' : 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Extra MLC Cost:</th><td class="text-danger fw-bold">{{ $customer->extra_mlc_amount ? '₹' . number_format($customer->extra_mlc_amount, 2) : 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Assigned Agent:</th><td>
                                        @if($customer->assignee)
                                            <span class="badge bg-primary">{{ $customer->assignee->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Unassigned</span>
                                        @endif
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </x-card>

                    <!-- We removed MLC section from here -->

                    <!-- Contractor Logs Card -->
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-user-shield"></i> Installation Contractor Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">LMC Contractor:</th><td>{{ $customer->contractor ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">RFC Contractor:</th><td>{{ $customer->rfcContractor->name ?? $customer->rfc_contractor ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">RFC Date:</th><td>{{ $customer->rfc_date ? $customer->rfc_date->format('d M Y') : 'N/A' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><th style="width: 45%;" class="text-secondary">JMR Contractor:</th><td>{{ $customer->jmrContractor->name ?? $customer->jmr_contractor ?? 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">JMR Date:</th><td>{{ $customer->jmr_date ? $customer->jmr_date->format('d M Y') : 'N/A' }}</td></tr>
                                    <tr><th class="text-secondary">Conversion Date:</th><td>
                                        @if($customer->conversion_date)
                                            <span class="badge bg-success font-monospace">{{ $customer->conversion_date->format('d M Y') }}</span>
                                        @else
                                            <span class="badge bg-secondary opacity-75">Pending</span>
                                        @endif
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Right Column: Installation Images & Reports -->
                <div class="col-md-5">
                    <x-card class="mb-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-camera"></i> Installation Photo Gallery</h5>
                        
                        <div class="row text-center">
                            <!-- Job Card -->
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-2 small text-dark"><i class="fas fa-file-pdf text-danger me-1"></i> Job Card File:</span>
                                    @if($customer->job_card)
                                        <div class="my-2 text-center flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 100px;">
                                            @if(Str::endsWith($customer->job_card, '.pdf'))
                                                <i class="fas fa-file-pdf text-danger fa-4x"></i>
                                            @else
                                                <img src="{{ asset('storage/' . $customer->job_card) }}" class="img-fluid rounded border" style="max-height: 100px;" alt="Job Card">
                                            @endif
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->job_card) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-1 mt-2">
                                            <i class="fas fa-external-link-alt"></i> View File
                                        </a>
                                    @else
                                        <span class="text-muted d-block my-4">No Job Card uploaded</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Meter Photo -->
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-2 small text-dark"><i class="fas fa-image text-primary me-1"></i> Meter/General Photo:</span>
                                    @if($customer->photo)
                                        <div class="my-2 text-center flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 100px;">
                                            <img src="{{ asset('storage/' . $customer->photo) }}" class="img-fluid rounded border" style="max-height: 100px;" alt="Meter Photo">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->photo) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-1 mt-2">
                                            <i class="fas fa-external-link-alt"></i> View Photo
                                        </a>
                                    @else
                                        <span class="text-muted d-block my-4">No Photo uploaded</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row text-center mt-2">
                            <!-- Inside Kitchen Photo -->
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-1 small text-dark" style="font-size: 11px;">Inside Kitchen:</span>
                                    @if($customer->inside_kitchen_photo)
                                        <div class="my-1 text-center">
                                            <img src="{{ asset('storage/' . $customer->inside_kitchen_photo) }}" class="img-fluid rounded border" style="max-height: 60px;" alt="Inside Kitchen">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->inside_kitchen_photo) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-0" style="font-size:10px;">View</a>
                                    @else
                                        <span class="text-muted d-block my-2" style="font-size: 11px;">Missing</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Outside Balcony Photo -->
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-1 small text-dark" style="font-size: 11px;">Outside Balcony:</span>
                                    @if($customer->outside_kitchen_photo)
                                        <div class="my-1 text-center">
                                            <img src="{{ asset('storage/' . $customer->outside_kitchen_photo) }}" class="img-fluid rounded border" style="max-height: 60px;" alt="Outside Balcony">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->outside_kitchen_photo) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-0" style="font-size:10px;">View</a>
                                    @else
                                        <span class="text-muted d-block my-2" style="font-size: 11px;">Missing</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Meter 3 Angles -->
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-1 small text-dark" style="font-size: 11px;">Meter 3-Angles:</span>
                                    @if($customer->meter_photo_3_angles)
                                        <div class="my-1 text-center">
                                            <img src="{{ asset('storage/' . $customer->meter_photo_3_angles) }}" class="img-fluid rounded border" style="max-height: 60px;" alt="Meter 3 Angles">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->meter_photo_3_angles) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-0" style="font-size:10px;">View</a>
                                    @else
                                        <span class="text-muted d-block my-2" style="font-size: 11px;">Missing</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row text-center mt-2">
                            <!-- RFC Report -->
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-2 small text-dark"><i class="fas fa-file-invoice text-success me-1"></i> RFC Report:</span>
                                    @if($customer->rfc_report_image)
                                        <div class="my-2 text-center flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 80px;">
                                            <img src="{{ asset('storage/' . $customer->rfc_report_image) }}" class="img-fluid rounded border" style="max-height: 80px;" alt="RFC Report">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->rfc_report_image) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-1 mt-2">View</a>
                                    @else
                                        <span class="text-muted d-block my-3">No Report uploaded</span>
                                    @endif
                                </div>
                            </div>

                            <!-- JMR Report -->
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <span class="fw-bold d-block mb-2 small text-dark"><i class="fas fa-file-invoice text-success me-1"></i> JMR Report:</span>
                                    @if($customer->jmr_report_image)
                                        <div class="my-2 text-center flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 80px;">
                                            <img src="{{ asset('storage/' . $customer->jmr_report_image) }}" class="img-fluid rounded border" style="max-height: 80px;" alt="JMR Report">
                                        </div>
                                        <a href="{{ asset('storage/' . $customer->jmr_report_image) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 py-1 mt-2">View</a>
                                    @else
                                        <span class="text-muted d-block my-3">No Report uploaded</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
