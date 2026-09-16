@extends('layouts.admin')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-tasks text-primary me-2"></i>Task Management</h3>
            <p class="text-muted small mb-0">Track project activities, employee assignments, customer data, subtasks, and billing progress</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fas fa-plus-circle me-1"></i> Create & Assign Task
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Assigned --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="customer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Customers --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} {{ $c->city ? "({$c->city})" : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Priorities --</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="progress" {{ request('status') == 'progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="task_date" class="form-control form-control-sm" value="{{ request('task_date') }}" onchange="this.form.submit()" title="Filter by Created Date">
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- View Nav Tabs (Kanban vs List) -->
    <ul class="nav nav-pills mb-4" id="taskTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-semibold" id="kanban-tab" data-bs-toggle="pill" data-bs-target="#kanban-view" type="button" role="tab">
                <i class="fas fa-columns me-1"></i> Kanban Board
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-semibold" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-view" type="button" role="tab">
                <i class="fas fa-list me-1"></i> List Table View
            </button>
        </li>
    </ul>

    <div class="tab-content" id="taskTabContent">
        <!-- 1. Kanban Board View -->
        <div class="tab-pane fade show active" id="kanban-view" role="tabpanel">
            <div class="row g-3">
                @php
                    $columns = [
                        'todo' => ['title' => 'To Do', 'color' => '#64748b', 'bg' => '#f8fafc', 'badge' => 'secondary'],
                        'progress' => ['title' => 'In Progress', 'color' => '#2563eb', 'bg' => '#eff6ff', 'badge' => 'primary'],
                        'review' => ['title' => 'Under Review', 'color' => '#d97706', 'bg' => '#fffbeb', 'badge' => 'warning text-dark'],
                        'done' => ['title' => 'Done & Completed', 'color' => '#16a34a', 'bg' => '#f0fdf4', 'badge' => 'success']
                    ];
                @endphp

                @foreach($columns as $statusKey => $col)
                <div class="col-xl-3 col-md-6">
                    <div class="rounded-4 p-3 border h-100" style="background: {{ $col['bg'] }}; border-color: #cbd5e1 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark" style="color: {{ $col['color'] }} !important;">
                                <i class="fas fa-circle me-1" style="font-size: 0.65rem;"></i> {{ $col['title'] }}
                            </h6>
                            <span class="badge rounded-pill bg-white text-dark border px-2 py-1 small">
                                {{ $tasksByStatus[$statusKey]->count() }}
                            </span>
                        </div>

                        <div class="kanban-column space-y-3" id="{{ $statusKey }}-column" style="min-height: 450px;">
                            @forelse($tasksByStatus[$statusKey] as $task)
                            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white" data-task-id="{{ $task->id }}" style="border: 1px solid #e2e8f0 !important; cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('select')) window.location='{{ route('admin.tasks.show', $task) }}'">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning text-dark' : 'info') }} px-2 py-0" style="font-size: 0.7rem;">
                                        {{ strtoupper($task->priority) }}
                                    </span>
                                    @if($task->due_date)
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-clock me-1"></i>{{ $task->due_date->format('d M') }}
                                    </small>
                                    @endif
                                </div>

                                <h6 class="fw-bold text-dark mb-1">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="text-dark text-decoration-none">
                                        {{ $task->title }}
                                    </a>
                                </h6>

                                @if($task->description)
                                <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">{{ Str::limit($task->description, 60) }}</p>
                                @endif

                                <!-- Customer Badge & Data Info -->
                                @if($task->customer)
                                <div class="p-2 rounded-3 mb-2 border" style="background: #f8fafc; font-size: 0.75rem;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary"><i class="fas fa-building me-1"></i>{{ Str::limit($task->customer->name, 20) }}</span>
                                        @if($task->customer->phone)
                                        <a href="tel:{{ $task->customer->phone }}" class="text-muted text-decoration-none" title="{{ $task->customer->phone }}"><i class="fas fa-phone"></i></a>
                                        @endif
                                    </div>
                                    @if($task->customer->invoices && $task->customer->invoices->count() > 0)
                                    <div class="text-muted mt-1">
                                        <i class="fas fa-file-invoice text-success me-1"></i> {{ $task->customer->invoices->count() }} Bills Attached
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Subtasks Checklist Progress -->
                                @php
                                    $checklist = $task->checklist ?? [];
                                    $tot = count($checklist);
                                    $done = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                                @endphp
                                @if($tot > 0)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="progress flex-grow-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ round(($done / $tot) * 100) }}%;"></div>
                                    </div>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ $done }}/{{ $tot }}</span>
                                </div>
                                @endif

                                <!-- Assignee info -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1" style="font-size: 0.75rem;">
                                    <span class="text-muted">
                                        <i class="fas fa-user-circle text-primary me-1"></i>
                                        <strong>{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</strong>
                                    </span>
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-light py-0 px-2 border" style="font-size: 0.7rem;">
                                        View <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted small fst-italic">No tasks in this column</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 2. List Table View -->
        <div class="tab-pane fade" id="list-view" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc;" class="small fw-bold text-secondary">
                                <tr>
                                    <th class="ps-4">Task Title</th>
                                    <th>Linked Customer</th>
                                    <th>Assigned Employee</th>
                                    <th>Priority</th>
                                    <th>Subtasks</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.tasks.show', $task) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">
                                            {{ $task->title }}
                                        </a>
                                        @if($task->description)
                                        <div class="text-muted small">{{ Str::limit($task->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->customer)
                                        <div class="fw-semibold text-dark">{{ $task->customer->name }}</div>
                                        <div class="text-muted small">{{ $task->customer->city ? $task->customer->city . ' - ' : '' }}{{ $task->customer->phone ?? '' }}</div>
                                        @else
                                        <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</div>
                                        <div class="text-muted small">{{ $task->creator ? 'By: ' . $task->creator->name : '' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning text-dark' : 'info') }} px-2 py-1">
                                            {{ strtoupper($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $checklist = $task->checklist ?? [];
                                            $tot = count($checklist);
                                            $done = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                                        @endphp
                                        @if($tot > 0)
                                        <span class="badge rounded-pill px-3 py-1" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                                            {{ $done }} / {{ $tot }} steps
                                        </span>
                                        @else
                                        <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $task->due_date ? $task->due_date->format('d M Y') : 'No deadline' }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-1" style="background: {{ $task->status == 'done' ? '#dcfce7' : ($task->status == 'progress' ? '#eff6ff' : '#f1f5f9') }}; color: {{ $task->status == 'done' ? '#15803d' : ($task->status == 'progress' ? '#1d4ed8' : '#475569') }}; font-weight: 600;">
                                            {{ strtoupper($task->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-light text-primary border" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-light text-secondary border" title="Edit Task">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">No tasks found. Create a new task to get started!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
