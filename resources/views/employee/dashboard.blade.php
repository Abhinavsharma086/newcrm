@extends('layouts.app')
@section('title', 'My Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Welcome, {{ auth()->user()->name }} 👋</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-tasks" title="My Active Tasks" :value="$stats['my_tasks']" color="navy"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-ticket-alt" title="My Open Tickets" :value="$stats['my_tickets']" color="red"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-funnel-dollar" title="My Active Leads" :value="$stats['my_leads']" color="info"/>
        </div>
        <div class="col-md-3">
            <x-stat-widget icon="fas fa-check-circle" title="Completed Tasks" :value="$stats['completed_tasks']" color="success"/>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <x-card title="My Recent Tasks">
                @forelse($recentTasks as $task)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <p class="mb-0 fw-500">{{ $task->title }}</p>
                        <small class="text-muted">Due: {{ $task->due_date ? $task->due_date->format('d M Y') : 'No due date' }}</small>
                    </div>
                    <div>
                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <span class="badge bg-secondary ms-1">{{ ucfirst($task->status) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No tasks assigned yet.</p>
                @endforelse
                <div class="mt-3">
                    <a href="{{ route('employee.tasks.index') }}" class="btn btn-sm btn-primary">View All Tasks</a>
                </div>
            </x-card>
        </div>

        <div class="col-md-6">
            <x-card title="My Recent Tickets">
                @forelse($recentTickets as $ticket)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <p class="mb-0 fw-500">{{ $ticket->ticket_no }} — {{ Str::limit($ticket->title, 35) }}</p>
                        <small class="text-muted">{{ $ticket->customer ? $ticket->customer->name : 'Internal' }}</small>
                    </div>
                    <span class="badge status-{{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-3">No tickets assigned yet.</p>
                @endforelse
                <div class="mt-3">
                    <a href="{{ route('employee.tickets.index') }}" class="btn btn-sm btn-primary">View All Tickets</a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
