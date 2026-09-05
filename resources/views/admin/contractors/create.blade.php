@extends('layouts.app')
@section('title', 'Add Contractor')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.contractors.index') }}">Contractors</a></li>
<li class="breadcrumb-item active">Add</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <x-card>
                <h5 class="mb-4"><i class="fas fa-hard-hat text-warning me-2"></i>Add Contractor</h5>
                <form action="{{ route('admin.contractors.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Contractor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Hope Infra" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="rfc" {{ old('type') == 'rfc' ? 'selected' : '' }}>RFC Contractor</option>
                                <option value="jmr" {{ old('type') == 'jmr' ? 'selected' : '' }}>JMR Contractor</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person"
                                   value="{{ old('contact_person') }}" placeholder="Person name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="{{ old('phone') }}" placeholder="Contact number">
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        <a href="{{ route('admin.contractors.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
