@extends('layouts.admin')

@section('title', 'Add New Account')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add New Account</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.accounts.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('account_code') is-invalid @enderror" 
                       id="account_code" name="account_code" value="{{ old('account_code') }}" required>
                @error('account_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                       id="account_name" name="account_name" value="{{ old('account_name') }}" required>
                @error('account_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                <select class="form-select @error('account_type') is-invalid @enderror" 
                        id="account_type" name="account_type" required>
                    <option value="">Select Type</option>
                    <option value="asset" {{ old('account_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                    <option value="liability" {{ old('account_type') == 'liability' ? 'selected' : '' }}>Liability</option>
                    <option value="equity" {{ old('account_type') == 'equity' ? 'selected' : '' }}>Equity</option>
                    <option value="income" {{ old('account_type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('account_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                @error('account_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="parent_id" class="form-label">Parent Account</label>
                <select class="form-select @error('parent_id') is-invalid @enderror" 
                        id="parent_id" name="parent_id">
                    <option value="">None (Top Level Account)</option>
                    @foreach($parentAccounts as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->account_code }} - {{ $parent->account_name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="opening_balance" class="form-label">Opening Balance <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" 
                       id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" required>
                @error('opening_balance')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Account
                </button>
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
