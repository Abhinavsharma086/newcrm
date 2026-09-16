@extends('layouts.app')
@section('title', 'Settings')
@section('breadcrumb')
<li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Company Settings</h2>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            {{-- Company Info --}}
            <div class="col-md-6">
                <x-card title="Company Information">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $settings['company_name']) }}" required>
                        @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">GSTIN</label>
                        <input type="text" name="company_gstin" class="form-control @error('company_gstin') is-invalid @enderror"
                               value="{{ old('company_gstin', $settings['company_gstin']) }}"
                               placeholder="22AAAAA0000A1Z5" maxlength="15">
                        @error('company_gstin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State (for GST calculation)</label>
                        <input type="text" name="company_state" class="form-control @error('company_state') is-invalid @enderror"
                               value="{{ old('company_state', $settings['company_state']) }}" required>
                        @error('company_state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="company_address" class="form-control" rows="3">{{ old('company_address', $settings['company_address']) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="company_phone" class="form-control"
                               value="{{ old('company_phone', $settings['company_phone']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="company_email" class="form-control @error('company_email') is-invalid @enderror"
                               value="{{ old('company_email', $settings['company_email']) }}">
                        @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </x-card>
            </div>

            {{-- WhatsApp API --}}
            <div class="col-md-6">
                <x-card title="WhatsApp Business API">
                    <div class="alert alert-info small">
                        <i class="fab fa-whatsapp me-1"></i>
                        Configure WhatsApp Business API to auto-capture customer leads from incoming messages.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API URL</label>
                        <input type="url" name="whatsapp_api_url" class="form-control"
                               value="{{ old('whatsapp_api_url', $settings['whatsapp_api_url']) }}"
                               placeholder="https://graph.facebook.com/v17.0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="password" name="whatsapp_api_token" class="form-control"
                               value="{{ old('whatsapp_api_token', $settings['whatsapp_api_token']) }}"
                               placeholder="Bearer token">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" class="form-control"
                               value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']) }}"
                               placeholder="WhatsApp Phone Number ID">
                    </div>
                    <div class="alert alert-secondary small">
                        <strong>Webhook URL:</strong><br>
                        <code>{{ url('/admin/whatsapp/webhook') }}</code>
                    </div>
                </x-card>
            </div>

            {{-- Payment / Bank Details --}}
            <div class="col-md-6">
                <x-card title="Payment & Bank Details">
                    <div class="alert alert-info small">
                        <i class="fas fa-university me-1"></i>
                        These details will be printed on the invoice when "Include Payment Details" is checked.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control"
                               value="{{ old('bank_name', \App\Models\CompanySetting::get('bank_name', '')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="bank_account_name" class="form-control"
                               value="{{ old('bank_account_name', \App\Models\CompanySetting::get('bank_account_name', '')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="bank_account_number" class="form-control"
                               value="{{ old('bank_account_number', \App\Models\CompanySetting::get('bank_account_number', '')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="form-control"
                               value="{{ old('bank_ifsc', \App\Models\CompanySetting::get('bank_ifsc', '')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control"
                               value="{{ old('upi_id', \App\Models\CompanySetting::get('upi_id', '')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UPI QR Code Image</label>
                        @if(\App\Models\CompanySetting::get('upi_qr_image'))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . \App\Models\CompanySetting::get('upi_qr_image')) }}" alt="UPI QR" style="max-height: 100px; border: 1px solid #ccc; padding: 2px;">
                            </div>
                        @endif
                        <input type="file" name="upi_qr_image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload a square QR code image for scanning (JPG/PNG).</small>
                    </div>
                </x-card>
            </div>

            {{-- SKU Format Settings --}}
            <div class="col-md-12">
                <x-card title="SKU Format Settings">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-barcode me-1"></i> Customize how SKUs are automatically generated for different item types.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Material Prefix</label>
                            <input type="text" id="material_sku_prefix" name="material_sku_prefix" class="form-control" 
                                   value="{{ old('material_sku_prefix', $settings['material_sku_prefix']) }}" placeholder="e.g. MAT">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Service Prefix</label>
                            <input type="text" id="service_sku_prefix" name="service_sku_prefix" class="form-control" 
                                   value="{{ old('service_sku_prefix', $settings['service_sku_prefix']) }}" placeholder="e.g. SER">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Separator</label>
                            <select id="sku_separator" name="sku_separator" class="form-select">
                                <option value="-" {{ old('sku_separator', $settings['sku_separator']) == '-' ? 'selected' : '' }}>Dash (-)</option>
                                <option value="_" {{ old('sku_separator', $settings['sku_separator']) == '_' ? 'selected' : '' }}>Underscore (_)</option>
                                <option value="/" {{ old('sku_separator', $settings['sku_separator']) == '/' ? 'selected' : '' }}>Slash (/)</option>
                                <option value="" {{ old('sku_separator', $settings['sku_separator']) == '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Number of Digits</label>
                            <input type="number" id="sku_digits" name="sku_digits" class="form-control" 
                                   value="{{ old('sku_digits', $settings['sku_digits']) }}" min="1" max="10">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Starting Number</label>
                            <input type="number" id="sku_start_number" name="sku_start_number" class="form-control" 
                                   value="{{ old('sku_start_number', $settings['sku_start_number']) }}" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Suffix (Optional)</label>
                            <input type="text" id="sku_suffix" name="sku_suffix" class="form-control" 
                                   value="{{ old('sku_suffix', $settings['sku_suffix']) }}" placeholder="e.g. 2026">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="button" id="resetSkuBtn" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light border rounded">
                        <div class="row align-items-center mb-2">
                            <div class="col-md-3">
                                <span class="text-muted fw-bold">Material Preview:</span>
                            </div>
                            <div class="col-md-9">
                                <input type="text" id="material_sku_preview" class="form-control fw-bold text-primary font-monospace" readonly style="background-color: #fff; border: 1px dashed #0d6efd;">
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="text-muted fw-bold">Service Preview:</span>
                            </div>
                            <div class="col-md-9">
                                <input type="text" id="service_sku_preview" class="form-control fw-bold text-success font-monospace" readonly style="background-color: #fff; border: 1px dashed #198754;">
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">This format will be applied automatically when creating new products or services. Existing product SKUs will not be changed.</small>
                    </div>
                </x-card>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const matPrefixEl = document.getElementById('material_sku_prefix');
    const serPrefixEl = document.getElementById('service_sku_prefix');
    const separatorEl = document.getElementById('sku_separator');
    const digitsEl = document.getElementById('sku_digits');
    const startNumEl = document.getElementById('sku_start_number');
    const suffixEl = document.getElementById('sku_suffix');
    const matPreviewEl = document.getElementById('material_sku_preview');
    const serPreviewEl = document.getElementById('service_sku_preview');
    const resetBtn = document.getElementById('resetSkuBtn');

    function updatePreview() {
        const matPrefix = matPrefixEl.value.trim();
        const serPrefix = serPrefixEl.value.trim();
        const sep = separatorEl.value;
        const digits = parseInt(digitsEl.value) || 4;
        const startNum = startNumEl.value.trim() || '1';
        const suffix = suffixEl.value.trim();

        let numStr = startNum.padStart(digits, '0');
        
        let matPreview = '';
        if (matPrefix) matPreview += matPrefix + sep;
        matPreview += numStr;
        if (suffix) matPreview += sep + suffix;
        matPreviewEl.value = matPreview;

        let serPreview = '';
        if (serPrefix) serPreview += serPrefix + sep;
        serPreview += numStr;
        if (suffix) serPreview += sep + suffix;
        serPreviewEl.value = serPreview;
    }

    // Attach listeners
    [matPrefixEl, serPrefixEl, separatorEl, digitsEl, startNumEl, suffixEl].forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    // Reset button
    resetBtn.addEventListener('click', function() {
        matPrefixEl.value = 'MAT';
        serPrefixEl.value = 'SER';
        separatorEl.value = '-';
        digitsEl.value = 4;
        startNumEl.value = 1;
        suffixEl.value = '';
        updatePreview();
    });

    // Initial preview
    updatePreview();
});
</script>
@endpush
