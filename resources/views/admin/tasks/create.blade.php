@extends('layouts.admin')

@section('title', 'Create New Task')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-tasks text-primary me-2"></i>Create New Task</h3>
            <p class="text-muted small mb-0">Assign tasks to team members, link customer records, and build subtask checklists</p>
        </div>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Tasks
        </a>
    </div>

    <form action="{{ route('admin.tasks.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Task Details Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i>Task Info & Scope</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="title" class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Complete GST Bill audit for Metric Qube Energy">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold">Task Description & Instructions</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Detail the work requirements, site details, and delivery deliverables...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subtasks / Checklist Items -->
                        <div class="p-3 rounded-4 border mb-2" style="background: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-dark mb-0"><i class="fas fa-list-check text-primary me-1"></i> Subtask Checklist (Actionable Steps)</label>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" id="addChecklistBtn">
                                    <i class="fas fa-plus me-1"></i> Add Step
                                </button>
                            </div>
                            <div id="checklistContainer">
                                <div class="input-group input-group-sm mb-2 checklist-item">
                                    <span class="input-group-text bg-white"><i class="fas fa-circle-dot text-muted"></i></span>
                                    <input type="text" class="form-control" name="checklist_items[]" placeholder="e.g. Verify customer GSTIN and state code">
                                    <button class="btn btn-outline-danger" type="button" onclick="this.closest('.checklist-item').remove()"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- Assignment & Relations Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-tag text-primary me-2"></i>Assignment & Customer</h6>
                    </div>
                    <div class="card-body p-4">
                        <!-- Employee Assignment -->
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label small fw-semibold"><i class="fas fa-user-check text-primary me-1"></i> Assign to Employee <span class="text-danger">*</span></label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                <option value="">-- Select Team Member --</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->email }})
                                </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Customer Linking -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label small fw-semibold"><i class="fas fa-building text-primary me-1"></i> Link Customer (Optional)</label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                <option value="">-- Select Customer / Client --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->city ? "({$c->city})" : '' }} {{ $c->phone ? "- {$c->phone}" : '' }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Linking a customer attaches their billing records, quotes, and contact details directly to this task.</div>
                        </div>

                        <!-- Priority & Status -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="priority" class="form-label small fw-semibold">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="status" class="form-label small fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="todo" {{ old('status', 'todo') == 'todo' ? 'selected' : '' }}>To Do</option>
                                    <option value="progress" {{ old('status') == 'progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="review" {{ old('status') == 'review' ? 'selected' : '' }}>Review</option>
                                    <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="mb-4">
                            <label for="due_date" class="form-label small fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold">
                                <i class="fas fa-plus-circle me-1"></i> Create & Assign Task
                            </button>
                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('addChecklistBtn').addEventListener('click', function() {
    const container = document.getElementById('checklistContainer');
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm mb-2 checklist-item';
    div.innerHTML = `
        <span class="input-group-text bg-white"><i class="fas fa-circle-dot text-muted"></i></span>
        <input type="text" class="form-control" name="checklist_items[]" placeholder="Subtask step...">
        <button class="btn btn-outline-danger" type="button" onclick="this.closest('.checklist-item').remove()"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(div);
});
</script>
@endpush
@endsection
