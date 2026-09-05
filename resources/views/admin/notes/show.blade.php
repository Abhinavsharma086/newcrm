@extends('layouts.app')

@section('title', 'Note Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.notes.index') }}">Credit & Debit Notes</a></li>
<li class="breadcrumb-item active">Note Details</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Credit/Debit Note Details</h2>
        <div>
            <a href="{{ route('admin.notes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            <x-card>
                <div class="text-center mb-4">
                    <h4>{{ strtoupper($note->type) }} NOTE</h4>
                    <h3><code>{{ $note->note_no }}</code></h3>
                    <p class="text-muted">Issued on {{ \Carbon\Carbon::parse($note->note_date)->format('d M Y') }}</p>
                </div>
                
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 35%;">Linked Invoice</th>
                        <td>
                            <a href="{{ route('admin.invoices.show', $note->invoice) }}">
                                <code>{{ $note->invoice->invoice_no }}</code>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Customer</th>
                        <td>{{ $note->invoice->customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Adjusted Amount (Excl. Tax)</th>
                        <td>₹{{ number_format($note->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Adjusted Tax (GST)</th>
                        <td>₹{{ number_format($note->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="table-info fw-bold">
                        <th>Total Refund / Charge</th>
                        <td>₹{{ number_format($note->amount + $note->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Reason / Description</th>
                        <td>{{ $note->reason }}</td>
                    </tr>
                    <tr>
                        <th>Issued By</th>
                        <td>{{ $note->creator->name }}</td>
                    </tr>
                </table>
            </x-card>
        </div>
    </div>

</div>
@endsection
