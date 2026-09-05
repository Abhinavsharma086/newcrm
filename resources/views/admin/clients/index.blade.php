@extends('layouts.app')
@section('title', 'Clients')
@section('breadcrumb')
<li class="breadcrumb-item active">Clients</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-handshake text-info me-2"></i>Clients Directory</h2>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Client
        </a>
    </div>
    <x-card>
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $client->name }}</strong></td>
                    <td>{{ $client->contact_person ?? '-' }}</td>
                    <td>{{ $client->phone ?? '-' }}</td>
                    <td>{{ $client->email ?? '-' }}</td>
                    <td>
                        <form action="{{ route('admin.clients.toggle', $client) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-{{ $client->is_active ? 'success' : 'secondary' }}">
                                <i class="fas fa-power-off"></i> {{ $client->is_active ? 'Active (Click to Deactivate)' : 'Inactive (Click to Activate)' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Delete client?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No clients configured.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
