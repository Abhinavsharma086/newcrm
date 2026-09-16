@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <x-card>
            <h5 class="mb-4">Edit Supplier</h5>
            <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">GSTIN (Optional)</label>
                    <div class="position-relative">
                        <input type="text" name="gst_number" id="gst_number" class="form-control text-uppercase" value="{{ old('gst_number', $supplier->gst_number) }}" placeholder="15-character GSTIN" maxlength="15">
                        <div id="gstSpinner" class="spinner-border spinner-border-sm text-primary position-absolute d-none" role="status" style="right: 10px; top: 10px;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone 1</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone 2</label>
                    <input type="text" name="phone_2" class="form-control" value="{{ old('phone_2', $supplier->phone_2) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ $supplier->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-warning">Update Supplier</button>
            </form>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
const gstInput = document.getElementById('gst_number');
if (gstInput) {
    gstInput.addEventListener('input', function() {
        const gstin = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        this.value = gstin;
        
        if (gstin.length === 15) {
            const spinner = document.getElementById('gstSpinner');
            if (spinner) spinner.classList.remove('d-none');
            
            fetch(`/admin/api/verify-gstin/${gstin}`)
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
                    if (data.success && data.data) {
                        const name = data.data.name || data.data.legal_name || data.data.trade_name;
                        if (document.querySelector('input[name="name"]').value === '') {
                            document.querySelector('input[name="name"]').value = name;
                        }
                        if (document.querySelector('textarea[name="address"]').value === '') {
                            document.querySelector('textarea[name="address"]').value = data.data.address || '';
                        }
                    }
                })
                .catch(error => {
                    if (spinner) spinner.classList.add('d-none');
                    console.error('Error fetching GST details:', error);
                });
        }
    });
}
</script>
@endpush
