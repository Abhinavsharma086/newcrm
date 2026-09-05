@extends('layouts.app')
@section('title', 'My Tasks')
@section('breadcrumb')
<li class="breadcrumb-item active">My Tasks</li>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">My Tasks — Kanban Board</h2>

    <div class="row g-3">
        @foreach(['todo' => ['label'=>'To-Do','color'=>'secondary'], 'progress' => ['label'=>'In Progress','color'=>'primary'], 'review' => ['label'=>'Review','color'=>'warning'], 'done' => ['label'=>'Done','color'=>'success']] as $status => $meta)
        <div class="col-md-3">
            <div class="kanban-column">
                <h5 class="mb-3">
                    <i class="fas fa-circle text-{{ $meta['color'] }}"></i> {{ $meta['label'] }}
                    <span class="badge bg-{{ $meta['color'] }} float-end">{{ $tasksByStatus[$status]->count() }}</span>
                </h5>
                <div id="{{ $status }}-column">
                    @foreach($tasksByStatus[$status] as $task)
                    <div class="kanban-card" data-task-id="{{ $task->id }}">
                        <h6>{{ $task->title }}</h6>
                        <p class="small text-muted mb-2">{{ Str::limit($task->description, 50) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                            @if($task->due_date)
                            <small class="text-muted"><i class="fas fa-calendar"></i> {{ $task->due_date->format('d M') }}</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
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
