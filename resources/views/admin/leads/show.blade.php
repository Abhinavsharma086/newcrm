@extends('layouts.admin')
@section('title', 'Lead Details')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead Details</h5>
        <div>
            <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="30%">Title:</th><td>{{ $lead->title }}</td></tr>
                    <tr><th>Customer:</th><td>{{ $lead->customer->name ?? 'N/A' }}</td></tr>
                    <tr><th>Stage:</th><td><span class="badge bg-primary">{{ ucfirst($lead->stage) }}</span></td></tr>
                    <tr><th>Source:</th><td>{{ $lead->source }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="30%">Value:</th><td>₹{{ number_format($lead->value ?? 0, 2) }}</td></tr>
                    <tr><th>Assigned To:</th><td>{{ $lead->assignee->name ?? 'Unassigned' }}</td></tr>
                    <tr><th>Follow Up:</th><td>{{ $lead->follow_up_date?->format('d M Y') ?? 'N/A' }}</td></tr>
                    <tr><th>Created:</th><td>{{ $lead->created_at->format('d M Y') }}</td></tr>
                </table>
            </div>
        </div>
        @if($lead->notes)
        <div class="mt-3">
            <h6>Notes:</h6>
            <p>{{ $lead->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
