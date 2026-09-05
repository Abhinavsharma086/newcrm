@extends('layouts.app')

@section('title', 'Credit / Debit Notes')

@section('breadcrumb')
<li class="breadcrumb-item active">Credit / Debit Notes</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Credit & Debit Notes</h2>
        <a href="{{ route('admin.notes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Generate Note
        </a>
    </div>
    
    <x-card>
        <table id="notesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Note No</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Invoice Reference</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Tax Amount</th>
                    <th>Reason</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notes as $note)
                <tr>
                    <td><code>{{ $note->note_no }}</code></td>
                    <td>
                        <span class="badge bg-{{ $note->type == 'credit' ? 'success' : 'primary' }}">
                            {{ strtoupper($note->type) }}
                        </span>
                    </td>
                    <td>{{ $note->note_date->format('d M Y') }}</td>
                    <td><code>{{ $note->invoice->invoice_no }}</code></td>
                    <td>{{ $note->invoice->customer->name }}</td>
                    <td>₹{{ number_format($note->amount, 2) }}</td>
                    <td>₹{{ number_format($note->tax_amount, 2) }}</td>
                    <td>{{ $note->reason }}</td>
                    <td>{{ $note->creator->name }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.notes.show', $note) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-danger" onclick="deleteNote({{ $note->id }})">
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
    $('#notesTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });
});

function deleteNote(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the note!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ED1C24',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/notes/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
