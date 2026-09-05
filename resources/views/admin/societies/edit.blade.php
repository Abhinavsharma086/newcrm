@extends('layouts.app')

@section('title', 'Edit Society')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.societies.index') }}">Societies</a></li>
<li class="breadcrumb-item active">Edit Society</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Society</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.societies.update', $society) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Society Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $society->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contractor_id" class="form-label">Assigned Contractor</label>
                    <select class="form-select @error('contractor_id') is-invalid @enderror" id="contractor_id" name="contractor_id">
                        <option value="">Select Default Contractor</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}" {{ old('contractor_id', $society->contractor_id) == $contractor->id ? 'selected' : '' }}>
                                {{ $contractor->name }} ({{ strtoupper($contractor->type) }})
                            </option>
                        @endforeach
                    </select>
                    @error('contractor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Customers registered under this society will automatically be linked to this contractor.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Society
                    </button>
                    <a href="{{ route('admin.societies.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
