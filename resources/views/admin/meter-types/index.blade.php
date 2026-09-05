@extends('layouts.app')

@section('title', 'Meter Types')

@section('breadcrumb')
<li class="breadcrumb-item active">Meter Types</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tachometer-alt text-primary me-2"></i>Meter Types</h2>
        <a href="{{ route('admin.meter-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Meter Type
        </a>
    </div>

    <x-card>
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th width="100">Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meterTypes as $mt)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $mt->name }}</strong></td>
                    <td>{{ $mt->description ?? '-' }}</td>
                    <td>
                        @if($mt->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.meter-types.edit', $mt) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.meter-types.destroy', $mt) }}" method="POST"
                                  onsubmit="return confirm('Delete this meter type?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-tachometer-alt fa-2x mb-2 d-block"></i>
                        No meter types added yet.<br>
                        <a href="{{ route('admin.meter-types.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-plus"></i> Add First Meter Type
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
