@extends('layouts.app')
@section('title', 'Warehouses')
@section('breadcrumb')
<li class="breadcrumb-item active">Warehouses</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warehouses</h2>
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Warehouse
        </a>
    </div>

    <div class="row g-3">
        @forelse($warehouses as $warehouse)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box navy me-3" style="width:45px;height:45px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-warehouse text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $warehouse->name }}</h5>
                            <small class="text-muted">{{ $warehouse->location }}</small>
                        </div>
                    </div>
                    <p class="mb-1"><i class="fas fa-user-tie me-2 text-muted"></i>
                        Manager: {{ $warehouse->manager ? $warehouse->manager->name : 'Not assigned' }}
                    </p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-danger" onclick="deleteWarehouse({{ $warehouse->id }})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">No warehouses found. <a href="{{ route('admin.warehouses.create') }}">Create one</a>.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteWarehouse(id) {
    Swal.fire({ title: 'Delete Warehouse?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ED1C24', confirmButtonText: 'Yes, delete!' })
    .then(r => {
        if (r.isConfirmed) {
            let f = document.createElement('form');
            f.method = 'POST'; f.action = '/admin/warehouses/' + id;
            f.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(f); f.submit();
        }
    });
}
</script>
@endpush
