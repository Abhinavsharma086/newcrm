@extends('layouts.admin')

@section('title', 'Add Lead')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add New Lead</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.leads.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="customer_name" class="form-label">Customer</label>
                    <select class="form-select select2-tags @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name">
                        <option value="">Select Customer or Type Below (Optional)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->name }}" {{ old('customer_name') == $customer->name ? 'selected' : '' }}>
                                {{ $customer->name }} {{ $customer->company_name ? '(' . $customer->company_name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="stage" class="form-label">Stage <span class="text-danger">*</span></label>
                    <select class="form-select @error('stage') is-invalid @enderror" 
                            id="stage" name="stage" required>
                        <option value="new" {{ old('stage') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ old('stage') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ old('stage') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ old('stage') == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ old('stage') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                    @error('stage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('source') is-invalid @enderror" 
                           id="source" name="source" value="{{ old('source') }}" required>
                    @error('source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" 
                           id="value" name="value" value="{{ old('value') }}">
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select class="form-select @error('assigned_to') is-invalid @enderror" 
                            id="assigned_to" name="assigned_to">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('assigned_to') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="follow_up_date" class="form-label">Follow Up Date</label>
                <input type="date" class="form-control @error('follow_up_date') is-invalid @enderror" 
                       id="follow_up_date" name="follow_up_date" value="{{ old('follow_up_date') }}">
                @error('follow_up_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Lead
                </button>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
