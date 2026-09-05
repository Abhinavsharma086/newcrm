@extends('layouts.admin')

@section('title', 'Add Warehouse')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add New Warehouse</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.warehouses.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                <textarea class="form-control @error('location') is-invalid @enderror" 
                          id="location" name="location" rows="3" required>{{ old('location') }}</textarea>
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="manager_id" class="form-label">Manager</label>
                <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                    <option value="">Select Manager</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
                @error('manager_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Warehouse
                </button>
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
