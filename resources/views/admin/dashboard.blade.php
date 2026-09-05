@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-stat-widget 
                icon="fas fa-users" 
                title="Active Employees" 
                :value="$stats['total_employees']" 
                color="navy" 
            />
        </div>
        <div class="col-md-3">
            <x-stat-widget 
                icon="fas fa-user-tie" 
                title="Total Customers" 
                :value="$stats['total_customers']" 
                color="red" 
            />
        </div>
        <div class="col-md-3">
            <x-stat-widget 
                icon="fas fa-rupee-sign" 
                title="Total Revenue" 
                :value="'₹' . number_format($stats['total_revenue'], 2)" 
                color="success" 
            />
        </div>
        <div class="col-md-3">
            <x-stat-widget 
                icon="fas fa-file-invoice-dollar" 
                title="Pending Invoices" 
                :value="$stats['pending_invoices']" 
                color="warning" 
            />
        </div>
    </div>

    <!-- Section 5: Operations Kanban Section -->
    <h4 class="mb-3 text-dark"><i class="fas fa-project-diagram text-primary me-2"></i>Operations Workflow (Connection Status)</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.customers.index', ['stage' => 'Registered']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-light text-dark h-100 position-relative hover-shadow transition">
                    <div class="card-body py-4 text-center">
                        <div class="icon-circle bg-secondary text-white mx-auto mb-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                        <h6 class="text-muted text-uppercase small mb-1">To Do</h6>
                        <h3 class="mb-0 fw-bold text-secondary">{{ $stats['todo_registrations'] }}</h3>
                        <small class="text-muted">New Registrations</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.customers.index', ['stage' => 'LMC Done']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-light text-dark h-100 position-relative hover-shadow transition">
                    <div class="card-body py-4 text-center">
                        <div class="icon-circle bg-warning text-dark mx-auto mb-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-tools fa-lg"></i>
                        </div>
                        <h6 class="text-muted text-uppercase small mb-1">In Progress</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['inprogress_lmc'] }}</h3>
                        <small class="text-muted">LMC Completed</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.customers.index', ['stage' => 'RFC Done']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-light text-dark h-100 position-relative hover-shadow transition">
                    <div class="card-body py-4 text-center">
                        <div class="icon-circle bg-primary text-white mx-auto mb-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                        <h6 class="text-muted text-uppercase small mb-1">Review</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ $stats['review_rfc'] }}</h3>
                        <small class="text-muted">RFC Testing Completed</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.customers.index', ['stage' => 'Converted']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-light text-dark h-100 position-relative hover-shadow transition">
                    <div class="card-body py-4 text-center">
                        <div class="icon-circle bg-success text-white mx-auto mb-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-fire fa-lg"></i>
                        </div>
                        <h6 class="text-muted text-uppercase small mb-1">Done</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['done_conversion'] }}</h3>
                        <small class="text-muted">Conversions Live</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <x-card title="Monthly Revenue Trend">
                <canvas id="revenueChart" height="80"></canvas>
            </x-card>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
        datasets: [{
            label: 'Revenue (₹)',
            data: {!! json_encode($monthlyRevenue->pluck('revenue')) !!},
            borderColor: '#ED1C24',
            backgroundColor: 'rgba(237, 28, 36, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
@endpush
