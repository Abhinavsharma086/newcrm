@extends('layouts.app')
@section('title', 'Add Meter Type')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.meter-types.index') }}">Meter Types</a></li>
<li class="breadcrumb-item active">Add</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card>
                <h5 class="mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Add Meter Type</h5>
                <form action="{{ route('admin.meter-types.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Smart Meter, Analog Meter" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2"
                                  placeholder="Optional description">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        <a href="{{ route('admin.meter-types.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
