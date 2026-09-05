@extends('layouts.app')

@section('title', 'Tickets')

@section('breadcrumb')
<li class="breadcrumb-item active">Tickets</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ticket Management</h2>
        <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Ticket
        </a>
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
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_no }}</td>
                    <td>{{ Str::limit($ticket->title, 40) }}</td>
                    <td>{{ $ticket->customer ? $ticket->customer->name : 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : 'info') }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge status-{{ $ticket->status }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td>{{ $ticket->assignee ? $ticket->assignee->name : 'Unassigned' }}</td>
                    <td>{{ $ticket->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
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
    $('#ticketsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        order: [[0, 'desc']]
    });
});
</script>
@endpush
