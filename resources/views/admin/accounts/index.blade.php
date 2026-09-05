@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('breadcrumb')
<li class="breadcrumb-item active">Chart of Accounts</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chart of Accounts</h2>
        <div>
            <a href="{{ route('admin.accounts.journal.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-journal-whills"></i> Journal Entries
            </a>
            <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Account
            </a>
        </div>
    </div>

    @php
        $types = ['asset' => ['label' => 'Assets', 'icon' => 'fas fa-coins', 'color' => 'success'],
                  'liability' => ['label' => 'Liabilities', 'icon' => 'fas fa-hand-holding-usd', 'color' => 'danger'],
                  'equity' => ['label' => 'Equity', 'icon' => 'fas fa-chart-pie', 'color' => 'primary'],
                  'income' => ['label' => 'Income', 'icon' => 'fas fa-arrow-up', 'color' => 'info'],
                  'expense' => ['label' => 'Expenses', 'icon' => 'fas fa-arrow-down', 'color' => 'warning']];
    @endphp

    @foreach($types as $type => $meta)
    @php $typeAccounts = $accounts->where('account_type', $type); @endphp
    @if($typeAccounts->count())
    <x-card>
        <div slot="title">
            <i class="{{ $meta['icon'] }} text-{{ $meta['color'] }}"></i> {{ $meta['label'] }}
        </div>
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Opening Balance</th>
                    <th>Current Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($typeAccounts as $account)
                <tr>
                    <td><code>{{ $account->account_code }}</code></td>
                    <td>{{ $account->account_name }}</td>
                    <td><span class="badge bg-{{ $meta['color'] }}">{{ ucfirst($type) }}</span></td>
                    <td>₹{{ number_format($account->opening_balance, 2) }}</td>
                    <td>₹{{ number_format($account->current_balance, 2) }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>
    <div class="mb-3"></div>
    @endif
    @endforeach
</div>
@endsection
