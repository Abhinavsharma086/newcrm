@extends('layouts.app')
@section('title', 'My Leads')
@section('breadcrumb')
<li class="breadcrumb-item active">My Leads</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Leads</h2>
    </div>

    <x-card>
        <table id="leadsTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Customer</th>
                    <th>Stage</th>
                    <th>Value</th>
                    <th>Follow-up</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $lead)
                @php
                    $stageColors = ['new'=>'secondary','contacted'=>'info','qualified'=>'primary','converted'=>'success','lost'=>'danger'];
                @endphp
                <tr>
                    <td>{{ $lead->title }}</td>
                    <td>{{ $lead->customer ? $lead->customer->name : '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $stageColors[$lead->stage] ?? 'secondary' }}">
                            {{ ucfirst($lead->stage) }}
                        </span>
                    </td>
                    <td>{{ $lead->value ? '₹' . number_format($lead->value, 2) : '—' }}</td>
                    <td>
                        @if($lead->follow_up_date)
                            @if($lead->follow_up_date->isPast())
                                <span class="text-danger"><i class="fas fa-clock"></i> {{ $lead->follow_up_date->format('d M Y') }}</span>
                            @else
                                {{ $lead->follow_up_date->format('d M Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('employee.leads.show', $lead) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
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
    $('#leadsTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'print'] });
});
</script>
@endpush
