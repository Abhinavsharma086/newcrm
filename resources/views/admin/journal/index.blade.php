@extends('layouts.app')
@section('title', 'Journal Entries')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.accounts.index') }}">Accounts</a></li>
<li class="breadcrumb-item active">Journal Entries</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Journal Entries</h2>
        <a href="{{ route('admin.accounts.journal.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Entry
        </a>
    </div>

    <x-card>
        <table id="journalTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Entry No</th>
                    <th>Date</th>
                    <th>Narration</th>
                    <th>Lines</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr>
                    <td><code>{{ $entry->entry_no }}</code></td>
                    <td>{{ $entry->date->format('d M Y') }}</td>
                    <td>{{ Str::limit($entry->narration, 50) }}</td>
                    <td><span class="badge bg-secondary">{{ $entry->lines->count() }} lines</span></td>
                    <td>{{ $entry->creator->name }}</td>
                    <td>
                        <a href="{{ route('admin.accounts.journal.show', $entry) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
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
    $('#journalTable').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'excel', 'pdf', 'print'] });
});
</script>
@endpush
