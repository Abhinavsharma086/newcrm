@extends('layouts.admin')
@section('title', 'Edit Lead')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Lead</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $lead->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $lead->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="stage" class="form-label">Stage <span class="text-danger">*</span></label>
                    <select class="form-select" id="stage" name="stage" required>
                        <option value="new" {{ old('stage', $lead->stage) == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ old('stage', $lead->stage) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ old('stage', $lead->stage) == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ old('stage', $lead->stage) == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ old('stage', $lead->stage) == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="source" name="source" value="{{ old('source', $lead->source) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="number" step="0.01" class="form-control" id="value" name="value" value="{{ old('value', $lead->value) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('assigned_to', $lead->assigned_to) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="follow_up_date" class="form-label">Follow Up Date</label>
                <input type="date" class="form-control" id="follow_up_date" name="follow_up_date" value="{{ old('follow_up_date', $lead->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $lead->notes) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Lead</button>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
