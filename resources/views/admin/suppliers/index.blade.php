@extends('layouts.app')
@section('title', 'Suppliers')
@section('breadcrumb')
<li class="breadcrumb-item active">Suppliers</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck-loading text-success me-2"></i>Suppliers Directory</h2>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Supplier
        </a>
    </div>
    <x-card>
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Contact Person</th>
                    <th>Phone 1</th>
                    <th>Phone 2</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->contact_person ?? '-' }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->phone_2 ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete supplier?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No suppliers configured.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
