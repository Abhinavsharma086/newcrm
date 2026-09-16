@extends('layouts.app')
@section('title', 'My Tasks')
@section('breadcrumb')
<li class="breadcrumb-item active">My Tasks</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Tasks — Kanban Board</h2>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="task_date" class="form-control" value="{{ request('task_date') }}" onchange="this.form.submit()" title="Filter by Created Date">
            @if(request('task_date'))
            <a href="{{ route('employee.tasks.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="fas fa-undo"></i></a>
            @endif
        </form>
    </div>

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
                    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white" data-task-id="{{ $task->id }}" style="border: 1px solid #e2e8f0 !important; cursor: pointer;" onclick="if(!event.target.closest('a')) window.location='{{ route('employee.tasks.show', $task) }}'">
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
                            <a href="{{ route('employee.tasks.show', $task) }}" class="text-dark text-decoration-none">
                                {{ $task->title }}
                            </a>
                        </h6>

                        @if($task->description)
                        <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">{{ Str::limit($task->description, 60) }}</p>
                        @endif

                        @php
                            $checklist = $task->checklist ?? [];
                            $tot = count($checklist);
                            $done = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                        @endphp
                        @if($tot > 0)
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ round(($done / $tot) * 100) }}%;"></div>
                            </div>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ $done }}/{{ $tot }}</span>
                        </div>
                        @endif
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
@endsection

@push('scripts')
<script>
['todo','progress','review','done'].forEach(status => {
    new Sortable(document.getElementById(status + '-column'), {
        group: 'shared', animation: 150,
        onEnd: function(evt) {
            const taskId = evt.item.getAttribute('data-task-id');
            const newStatus = evt.to.id.replace('-column', '');
            fetch(`/employee/tasks/${taskId}/status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status: newStatus })
            }).then(r => r.json()).then(d => {
                if (d.success) Swal.fire({ icon: 'success', title: 'Updated!', timer: 1200, showConfirmButton: false });
            });
        }
    });
});
</script>
@endpush
