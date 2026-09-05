@extends('layouts.app')

@section('title', 'Generate Credit/Debit Note')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.notes.index') }}">Credit & Debit Notes</a></li>
<li class="breadcrumb-item active">Generate Note</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="mb-4">
        <h2>Generate Credit / Debit Note</h2>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <x-card>
                <form action="{{ route('admin.notes.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Note Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="credit" {{ old('type') == 'credit' ? 'selected' : '' }}>Credit Note (Customer Refund / Discount)</option>
                            <option value="debit" {{ old('type') == 'debit' ? 'selected' : '' }}>Debit Note (Undercharged Invoice adjustment)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="invoice_id" class="form-label">Link to Invoice</label>
                        <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id" required>
                            <option value="">Select Invoice</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                    {{ $invoice->invoice_no }} - {{ $invoice->customer->name }} (Total: ₹{{ number_format($invoice->total, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('invoice_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Adjusted Amount (Excluding Tax)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tax_amount" class="form-label">Adjusted Tax Amount (GST)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control @error('tax_amount') is-invalid @enderror" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', 0) }}" required>
                        </div>
                        @error('tax_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="note_date" class="form-label">Note Date</label>
                        <input type="date" class="form-control @error('note_date') is-invalid @enderror" id="note_date" name="note_date" value="{{ old('note_date', date('Y-m-d')) }}" required>
                        @error('note_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason / Narration</label>
                        <input type="text" class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" value="{{ old('reason') }}" required>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.notes.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Generate Note</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

</div>
@endsection
