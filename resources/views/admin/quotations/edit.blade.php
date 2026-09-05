@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Quotation #{{ $quotation->quotation_no }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" onsubmit="return confirm('Attention: Quotation status can only be changed ONCE. Once saved, it will be locked and cannot be changed again. Do you want to proceed?')">
                        @csrf
                        @method('PUT')
                        
                        <div class="alert alert-warning d-flex align-items-center mb-3">
                            <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                            <div>
                                <strong>One-Time Status Update:</strong> Quotation status can only be changed <strong>once</strong>. Once you set the status, it will be permanently locked.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Select New Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="sent" {{ old('status', $quotation->status) == 'sent' ? 'selected' : '' }}>Sent (Dispatched to Customer)</option>
                                <option value="accepted" {{ old('status', $quotation->status) == 'accepted' ? 'selected' : '' }}>Accepted (Approved by Customer)</option>
                                <option value="rejected" {{ old('status', $quotation->status) == 'rejected' ? 'selected' : '' }}>Rejected (Declined by Customer)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-lock me-1"></i> Save & Lock Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
