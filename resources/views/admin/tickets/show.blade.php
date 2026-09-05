@extends('layouts.app')

@section('title', 'Ticket Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Tickets</a></li>
<li class="breadcrumb-item active">{{ $ticket->ticket_no }}</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ticket #{{ $ticket->ticket_no }}</h2>
        <div>
            <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <x-card>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4>{{ $ticket->title }}</h4>
                        @php
                            $priorityColor = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                            $statusColor = ['open' => 'primary', 'progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                        @endphp
                        <div>
                            <span class="badge bg-{{ $priorityColor[$ticket->priority] }}">
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                            <span class="badge bg-{{ $statusColor[$ticket->status] }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="text-muted mt-2">{{ $ticket->description }}</p>
                    </div>

                    @if($ticket->category)
                    <div class="mb-3">
                        <strong>Category:</strong> {{ $ticket->category }}
                    </div>
                    @endif

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Created:</strong> {{ $ticket->created_at->format('d M Y H:i') }}
                            </p>
                            <p class="mb-2">
                                <strong>Created By:</strong> {{ $ticket->creator->name ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                <strong>SLA Due:</strong> 
                                <span class="{{ $ticket->sla_due_at < now() && $ticket->status !== 'resolved' && $ticket->status !== 'closed' ? 'text-danger' : '' }}">
                                    {{ $ticket->sla_due_at->format('d M Y H:i') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if($ticket->resolved_at)
                            <p class="mb-2">
                                <strong>Resolved:</strong> {{ $ticket->resolved_at->format('d M Y H:i') }}
                            </p>
                            @endif
                            @if($ticket->closed_at)
                            <p class="mb-2">
                                <strong>Closed:</strong> {{ $ticket->closed_at->format('d M Y H:i') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card class="mt-3">
                <div class="card-header">
                    <h5>Comments ({{ $ticket->comments->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($ticket->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $comment->user->name }}</strong>
                            <small class="text-muted">{{ $comment->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <p class="mb-0 mt-2">{{ $comment->comment }}</p>
                    </div>
                    @empty
                    <p class="text-muted">No comments yet.</p>
                    @endforelse

                    <form action="{{ route('admin.tickets.comments.store', $ticket) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label for="comment" class="form-label">Add Comment</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="3" required></textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-comment"></i> Post Comment
                        </button>
                    </form>
                </div>
            </x-card>
        </div>

        <div class="col-md-4">
            <x-card>
                <div class="card-body">
                    <h5 class="mb-3">Ticket Details</h5>
                    
                    <p class="mb-2">
                        <strong>Ticket No:</strong><br>
                        {{ $ticket->ticket_no }}
                    </p>

                    @if($ticket->customer)
                    <hr>
                    <p class="mb-2">
                        <strong>Customer:</strong><br>
                        {{ $ticket->customer->name }}
                    </p>
                    @if($ticket->customer->company_name)
                    <p class="mb-2">
                        <strong>Company:</strong><br>
                        {{ $ticket->customer->company_name }}
                    </p>
                    @endif
                    <p class="mb-2">
                        <strong>Email:</strong><br>
                        {{ $ticket->customer->email }}
                    </p>
                    <p class="mb-2">
                        <strong>Phone:</strong><br>
                        {{ $ticket->customer->phone }}
                    </p>
                    @endif

                    <hr>
                    
                    <p class="mb-2">
                        <strong>Assigned To:</strong><br>
                        {{ $ticket->assignee->name ?? 'Unassigned' }}
                    </p>
                </div>
            </x-card>
        </div>
    </div>

</div>
@endsection
