@extends('layouts.app')

@section('title', 'Branch Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
<li class="breadcrumb-item active">Branch Details</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Branch: {{ $branch->name }} ({{ $branch->code }})</h2>
        <div>
            <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Branch
            </a>
            <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Branch Info -->
        <div class="col-md-4 mb-4">
            <x-card>
                <h5 class="card-title">General Information</h5>
                <hr>
                <p><strong>Code:</strong> <code>{{ $branch->code }}</code></p>
                <p><strong>City:</strong> {{ $branch->city }}</p>
                <p><strong>State:</strong> {{ $branch->state }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-{{ $branch->status == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($branch->status) }}
                    </span>
                </p>
            </x-card>
        </div>
        
        <!-- Tabbed Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-navy text-white">
                    <ul class="nav nav-tabs card-header-tabs" id="branchTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active text-dark" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Employees</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-dark" id="warehouses-tab" data-bs-toggle="tab" data-bs-target="#warehouses" type="button" role="tab">Warehouses</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-dark" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">Invoices</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="branchTabsContent">
                        
                        <!-- Employees Tab -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branch->users as $u)
                                    <tr>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>{{ $u->department }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No employees linked to this branch.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Warehouses Tab -->
                        <div class="tab-pane fade" id="warehouses" role="tabpanel">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branch->warehouses as $w)
                                    <tr>
                                        <td>{{ $w->name }}</td>
                                        <td>{{ $w->location }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No warehouses linked to this branch.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Invoices Tab -->
                        <div class="tab-pane fade" id="invoices" role="tabpanel">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branch->invoices as $i)
                                    <tr>
                                        <td><code>{{ $i->invoice_no }}</code></td>
                                        <td>₹{{ number_format($i->total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $i->payment_status == 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($i->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No invoices generated for this branch.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
