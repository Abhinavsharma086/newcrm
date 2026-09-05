@extends('layouts.app')
@section('title', 'My Tickets')
@section('breadcrumb')
<li class="breadcrumb-item active">My Tickets</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Tickets</h2>
    </div>

    <x-card>
        <table id="ticketsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Customer</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>SLA Due</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_no }}</td>
                    <td>{{ Str::limit($ticket->title, 40) }}</td>
                    <td>{{ $ticket->customer ? $ticket->customer->name : 'Internal' }}</td>
                    <td>
                        <span class="badge bg-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : 'info') }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td><span class="badge status-{{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                    <td>
                        @if($ticket->sla_due_at)
                            @if($ticket->sla_due_at->isPast() && !in_array($ticket->status, ['resolved','closed']))
                                <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Overdue</span>
                            @else
                                {{ $ticket->sla_due_at->format('d M, H:i') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('employee.tickets.show', $ticket) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#ticketsTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'print'] });
});
</script>
@endpush
