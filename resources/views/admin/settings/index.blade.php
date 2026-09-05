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
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
