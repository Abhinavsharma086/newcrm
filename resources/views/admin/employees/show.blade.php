@extends('layouts.admin')

@section('title', 'Employee Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee Details</h5>
        <div>
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Name:</th>
                        <td>{{ $employee->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $employee->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $employee->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Department:</th>
                        <td>{{ $employee->department ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Role:</th>
                        <td><span class="badge bg-primary">{{ ucfirst($employee->roles->first()?->name ?? 'N/A') }}</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span class="badge bg-{{ $employee->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($employee->status) }}</span></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $employee->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
