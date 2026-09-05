@extends('layouts.app')

@section('title', $section === 'technical' ? 'My LMC & Technical Updates' : ($section === 'mlc' ? 'My MLC (Meter) Details' : 'My Customer Registrations'))

@section('breadcrumb')
<li class="breadcrumb-item active">{{ $section === 'technical' ? 'LMC & Technical Updates' : ($section === 'mlc' ? 'My MLC Details' : 'My Registrations') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $section === 'technical' ? 'My LMC & Technical Updates' : ($section === 'mlc' ? 'My MLC (Meter) Details' : 'My Customer Registrations') }}</h2>
    </div>

    <x-card>
        <table id="employeeCustomersTable" class="table table-hover align-middle">
            @if($section === 'registration')
                <thead>
                    <tr>
                        <th>CRN Number</th>
                        <th>Customer Name</th>
                        <th>Society</th>
                        <th>Mobile</th>
                        <th>Registration Date</th>
                        <th class="text-center">KYC Docs</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td><code>{{ $customer->crn_no ?? 'N/A' }}</code></td>
                        <td class="fw-medium text-dark">{{ $customer->name }}</td>
                        <td>{{ $customer->society ?? 'N/A' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->registration_date ? $customer->registration_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <i class="fas fa-id-card {{ $customer->primary_id_file ? 'text-success' : 'text-muted opacity-40' }}" title="Aadhaar: {{ $customer->primary_id_number ?? 'Missing' }}"></i>
                                <i class="fas fa-user-circle {{ $customer->passport_photo ? 'text-success' : 'text-muted opacity-40' }}" title="Photo"></i>
                                <i class="fas fa-file-invoice {{ $customer->address_proof_file ? 'text-success' : 'text-muted opacity-40' }}" title="Address Proof"></i>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('employee.customers.show', [$customer, 'section' => 'registration']) }}" class="btn btn-info text-white" title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('employee.customers.edit', [$customer, 'section' => 'registration']) }}" class="btn btn-primary" title="Collect KYC Docs">
                                    <i class="fas fa-id-badge"></i> Upload KYC
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            @else
                <thead>
                    <tr>
                        <th>CRN Number</th>
                        <th>LMC ID</th>
                        <th>Customer Name</th>
                        <th>Society</th>
                        <th>Mobile</th>
                        <th>Meter No.</th>
                        <th>LMC Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td><code>{{ $customer->crn_no ?? 'N/A' }}</code></td>
                        <td>
                            @if($customer->lmc_id)
                                <span class="badge bg-dark">{{ $customer->lmc_id }}</span>
                            @else
                                <span class="text-muted small">Pending LMC</span>
                            @endif
                        </td>
                        <td class="fw-medium text-dark">{{ $customer->name }}</td>
                        <td>{{ $customer->society ?? 'N/A' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td><code>{{ $customer->meter_no ?? 'Pending' }}</code></td>
                        <td>{{ $customer->lmc_date ? $customer->lmc_date->format('d M Y') : 'Pending' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('employee.customers.show', [$customer, 'section' => 'technical']) }}" class="btn btn-info text-white" title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('employee.customers.edit', [$customer, 'section' => 'technical']) }}" class="btn btn-primary" title="Update LMC/RFC/JMR">
                                    <i class="fas fa-tools"></i> Update LMC
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    </x-card>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#employeeCustomersTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'print'],
        order: [[1, 'asc']]
    });
});
</script>
@endpush
