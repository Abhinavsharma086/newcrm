@extends('layouts.app')

@section('title', 'Societies Directory')

@section('breadcrumb')
<li class="breadcrumb-item active">Societies</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Societies Master List</h2>
        <a href="{{ route('admin.societies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Society
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card>
        <table id="societiesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Society Name</th>
                    <th>Default Contractor</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($societies as $society)
                <tr>
                    <td>{{ $society->id }}</td>
                    <td><strong>{{ $society->name }}</strong></td>
                    <td>
                        @if($society->contractor)
                            <span class="badge bg-info text-dark"><i class="fas fa-hard-hat me-1"></i>{{ $society->contractor->name }}</span>
                        @else
                            <span class="text-muted small">Not Assigned</span>
                        @endif
                    </td>
                    <td>{{ $society->created_at ? $society->created_at->format('d M Y') : 'N/A' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.societies.edit', $society) }}" class="btn btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.societies.destroy', $society) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this society?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
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
    $('#societiesTable').DataTable({
        order: [[1, 'asc']]
    });
});
</script>
@endpush
