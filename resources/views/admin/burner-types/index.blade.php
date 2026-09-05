@extends('layouts.app')

@section('title', 'Burner Types')

@section('breadcrumb')
<li class="breadcrumb-item active">Burner Types</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-fire text-danger me-2"></i>Burner Types</h2>
        <a href="{{ route('admin.burner-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Burner Type
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
                @forelse($burnerTypes as $bt)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $bt->name }}</strong></td>
                    <td>{{ $bt->description ?? '-' }}</td>
                    <td>
                        @if($bt->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.burner-types.edit', $bt) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.burner-types.destroy', $bt) }}" method="POST"
                                  onsubmit="return confirm('Delete this burner type?');" style="display:inline;">
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
                        <i class="fas fa-fire fa-2x mb-2 d-block"></i>
                        No burner types added yet.<br>
                        <a href="{{ route('admin.burner-types.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-plus"></i> Add First Burner Type
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
