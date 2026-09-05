@extends('layouts.app')
@section('title', 'Contractors')
@section('breadcrumb')
<li class="breadcrumb-item active">Contractors</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-hard-hat text-warning me-2"></i>Contractors</h2>
        <a href="{{ route('admin.contractors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Contractor
        </a>
    </div>

    <x-card>
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th width="120">Type</th>
                    <th width="90">Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contractors as $c)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td>{{ $c->contact_person ?? '-' }}</td>
                    <td>{{ $c->phone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $c->type == 'rfc' ? 'primary' : ($c->type == 'jmr' ? 'info' : 'secondary') }}">
                            {{ strtoupper($c->type) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.contractors.edit', $c) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.contractors.destroy', $c) }}" method="POST"
                                  onsubmit="return confirm('Delete this contractor?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-hard-hat fa-2x mb-2 d-block"></i>
                        No contractors added yet.<br>
                        <a href="{{ route('admin.contractors.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-plus"></i> Add First Contractor
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
