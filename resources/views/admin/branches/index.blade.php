@extends('layouts.app')

@section('title', 'Branches')

@section('breadcrumb')
<li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Branch Management</h2>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Branch
        </a>
    </div>
    
    <x-card>
        <table id="branchesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Branch Code</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                <tr>
                    <td><code>{{ $branch->code }}</code></td>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->city }}</td>
                    <td>{{ $branch->state }}</td>
                    <td>
                        <span class="badge bg-{{ $branch->status == 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger" onclick="deleteBranch({{ $branch->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
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
    $('#branchesTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });
});

function deleteBranch(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will deactivate the branch!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ED1C24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/branches/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
