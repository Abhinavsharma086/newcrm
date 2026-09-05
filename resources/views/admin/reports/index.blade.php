@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb')
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Reports</h2>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box red mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-percent fa-lg text-white"></i>
                    </div>
                </div>
                <h5>GST Summary</h5>
                <p class="text-muted small">CGST / SGST / IGST monthly breakdown with invoice details</p>
                <a href="{{ route('admin.reports.gst-summary') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box navy mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-chart-line fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Sales Report</h5>
                <p class="text-muted small">Revenue analysis, customer-wise and product-wise breakdown</p>
                <a href="{{ route('admin.reports.sales-report') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box success mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-boxes fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Inventory Report</h5>
                <p class="text-muted small">Stock levels, low stock alerts, and warehouse-wise distribution</p>
                <a href="{{ route('admin.reports.inventory-report') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box warning mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-users fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Customer Report</h5>
                <p class="text-muted small">Customer-wise purchase history and spending analysis</p>
                <a href="{{ route('admin.reports.customer-report') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box navy mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-balance-scale fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Balance Sheet</h5>
                <p class="text-muted small">Assets, liabilities and equity overview</p>
                <a href="{{ route('admin.accounts.balance-sheet') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box red mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-file-invoice-dollar fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Profit & Loss</h5>
                <p class="text-muted small">Income vs expenses and net profit/loss statement</p>
                <a href="{{ route('admin.accounts.profit-loss') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center p-4">
                <div class="mb-3">
                    <div class="icon-box success mx-auto" style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-calculator fa-lg text-white"></i>
                    </div>
                </div>
                <h5>Trial Balance</h5>
                <p class="text-muted small">Summary of all debits and credits in Chart of Accounts</p>
                <a href="{{ route('admin.reports.trial-balance') }}" class="btn btn-primary mt-auto">
                    <i class="fas fa-arrow-right"></i> View Report
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
