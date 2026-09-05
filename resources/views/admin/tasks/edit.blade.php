@extends('layouts.admin')

@section('title', 'Edit Task: ' . $task->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-edit text-primary me-2"></i>Edit Task #{{ $task->id }}</h3>
            <p class="text-muted small mb-0">Update task scope, employee assignment, customer link, and checklist steps</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-eye me-1"></i> View Task
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Tasks
            </a>
        </div>
    </div>

    <form action="{{ route('admin.tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')
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
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $task->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold">Task Description & Instructions</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $task->description) }}</textarea>
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
                                @php
                                    $checklist = old('checklist_items', $task->checklist ?? []);
                                @endphp
                                @forelse($checklist as $index => $item)
                                <div class="input-group input-group-sm mb-2 checklist-item">
                                    <div class="input-group-text bg-white">
                                        <input type="checkbox" name="checklist_items[{{ $index }}][completed]" value="1" {{ !empty($item['completed']) ? 'checked' : '' }} class="form-check-input mt-0">
                                    </div>
                                    <input type="hidden" name="checklist_items[{{ $index }}][id]" value="{{ $item['id'] ?? uniqid() }}">
                                    <input type="text" class="form-control" name="checklist_items[{{ $index }}][text]" value="{{ is_array($item) ? ($item['text'] ?? '') : $item }}" placeholder="Subtask step...">
                                    <button class="btn btn-outline-danger" type="button" onclick="this.closest('.checklist-item').remove()"><i class="fas fa-times"></i></button>
                                </div>
                                @empty
                                <div class="input-group input-group-sm mb-2 checklist-item">
                                    <div class="input-group-text bg-white">
                                        <input type="checkbox" name="checklist_items[0][completed]" value="1" class="form-check-input mt-0">
                                    </div>
                                    <input type="hidden" name="checklist_items[0][id]" value="{{ uniqid() }}">
                                    <input type="text" class="form-control" name="checklist_items[0][text]" placeholder="Subtask step...">
                                    <button class="btn btn-outline-danger" type="button" onclick="this.closest('.checklist-item').remove()"><i class="fas fa-times"></i></button>
                                </div>
                                @endforelse
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
                            <label for="assigned_to" class="form-label small fw-semibold"><i class="fas fa-user-check text-primary me-1"></i> Assign to Employee</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                <option value="">-- Select Team Member --</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('assigned_to', $task->assigned_to) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Linking -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label small fw-semibold"><i class="fas fa-building text-primary me-1"></i> Link Customer</label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                <option value="">-- Select Customer / Client --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $task->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->city ? "({$c->city})" : '' }} {{ $c->phone ? "- {$c->phone}" : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority & Status -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="priority" class="form-label small fw-semibold">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="status" class="form-label small fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="todo" {{ old('status', $task->status) == 'todo' ? 'selected' : '' }}>To Do</option>
                                    <option value="progress" {{ old('status', $task->status) == 'progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="review" {{ old('status', $task->status) == 'review' ? 'selected' : '' }}>Review</option>
                                    <option value="done" {{ old('status', $task->status) == 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="mb-4">
                            <label for="due_date" class="form-label small fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-outline-secondary rounded-pill py-2">
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
let checklistIdx = {{ count($task->checklist ?? []) + 10 }};
document.getElementById('addChecklistBtn').addEventListener('click', function() {
    const container = document.getElementById('checklistContainer');
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm mb-2 checklist-item';
    div.innerHTML = `
        <div class="input-group-text bg-white">
            <input type="checkbox" name="checklist_items[${checklistIdx}][completed]" value="1" class="form-check-input mt-0">
        </div>
        <input type="hidden" name="checklist_items[${checklistIdx}][id]" value="${Date.now()}">
        <input type="text" class="form-control" name="checklist_items[${checklistIdx}][text]" placeholder="Subtask step...">
        <button class="btn btn-outline-danger" type="button" onclick="this.closest('.checklist-item').remove()"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(div);
    checklistIdx++;
});
</script>
@endpush
@endsection
