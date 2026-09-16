@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard Overview</li>
@endsection

@section('content')
<div class="container-fluid pb-5">
    
    <!-- Header & Quick Actions -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.5px;">Welcome back, {{ auth()->user()->name }}! 👋</h2>
            <p class="text-muted mb-0">Here's what's happening with your business today.</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Add Customer</a>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-outline-primary bg-white"><i class="fas fa-file-invoice-dollar me-2"></i>Create Invoice</a>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-outline-primary bg-white"><i class="fas fa-user-tie me-2"></i>Add Employee</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary bg-white"><i class="fas fa-box me-2"></i>Add Product</a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary bg-white"><i class="fas fa-chart-pie me-2"></i>Reports</a>
        </div>
    </div>
    
    <!-- KPI Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="icon-box navy"><i class="fas fa-users"></i></div>
                        <p>Active Employees</p>
                        <h3 class="counter" data-target="{{ $stats['total_employees'] ?? 0 }}">0</h3>
                    </div>
                    <div style="width: 80px; height: 40px;"><canvas id="spark1"></canvas></div>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up me-1"></i> 5.2% <span class="text-muted fw-normal ms-1">from last month</span></div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="icon-box red"><i class="fas fa-user-tie"></i></div>
                        <p>Total Customers</p>
                        <h3 class="counter" data-target="{{ $stats['total_customers'] ?? 0 }}">0</h3>
                    </div>
                    <div style="width: 80px; height: 40px;"><canvas id="spark2"></canvas></div>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up me-1"></i> 12.8% <span class="text-muted fw-normal ms-1">from last month</span></div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="icon-box success"><i class="fas fa-rupee-sign"></i></div>
                        <p>Total Revenue</p>
                        <h3 class="counter is-currency" data-target="{{ $stats['total_revenue'] ?? 0 }}">0</h3>
                    </div>
                    <div style="width: 80px; height: 40px;"><canvas id="spark3"></canvas></div>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up me-1"></i> 8.4% <span class="text-muted fw-normal ms-1">from last month</span></div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="icon-box warning"><i class="fas fa-file-invoice-dollar"></i></div>
                        <p>Pending Invoices</p>
                        <h3 class="counter" data-target="{{ $stats['pending_invoices'] ?? 0 }}">0</h3>
                    </div>
                    <div style="width: 80px; height: 40px;"><canvas id="spark4"></canvas></div>
                </div>
                <div class="stat-trend negative"><i class="fas fa-arrow-down me-1"></i> 2.1% <span class="text-muted fw-normal ms-1">from last month</span></div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-chart-area text-primary me-2"></i>Revenue Overview</span>
                    <select class="form-select form-select-sm w-auto border-0 bg-light">
                        <option>Last 6 Months</option>
                        <option>This Year</option>
                    </select>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-chart-pie text-success me-2"></i>Customer Growth</span>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operations Kanban Section -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
        <h4 class="m-0 text-dark fw-bold" style="letter-spacing: -0.5px;"><i class="fas fa-project-diagram text-primary me-2"></i>Operations Workflow</h4>
        <a href="{{ route('admin.customers.index') }}" class="text-decoration-none fw-bold">View All Customers <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    
    <div class="kanban-board">
        <!-- TO DO -->
        <div class="kanban-column">
            <h6 class="d-flex justify-content-between align-items-center">
                TO DO <span class="badge bg-white text-dark border shadow-sm rounded-pill">{{ $stats['todo_registrations'] ?? 0 }}</span>
            </h6>
            <a href="{{ route('admin.customers.index', ['stage' => 'Registered']) }}" class="kanban-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill mb-2">High Priority</span>
                    <i class="fas fa-clipboard-list text-secondary"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">New Registrations</h6>
                <p class="text-muted small mb-3">Customers pending technical assignment.</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="far fa-clock me-1"></i> Pending action
                    </div>
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 24px; height: 24px; font-size: 10px;"><i class="fas fa-user"></i></div>
                </div>
            </a>
        </div>
        
        <!-- IN PROGRESS -->
        <div class="kanban-column">
            <h6 class="d-flex justify-content-between align-items-center">
                IN PROGRESS <span class="badge bg-warning text-dark border shadow-sm rounded-pill">{{ $stats['inprogress_lmc'] ?? 0 }}</span>
            </h6>
            <a href="{{ route('admin.customers.index', ['stage' => 'LMC Done']) }}" class="kanban-card border-warning border-opacity-25 border-start-3" style="border-left-width: 4px; border-left-color: #f59e0b;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill mb-2">In Progress</span>
                    <i class="fas fa-tools text-warning"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">LMC Completed</h6>
                <p class="text-muted small mb-3">Customers ready for RFC testing.</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="progress w-50" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small fw-bold text-muted">50%</span>
                </div>
            </a>
        </div>

        <!-- REVIEW -->
        <div class="kanban-column">
            <h6 class="d-flex justify-content-between align-items-center">
                REVIEW <span class="badge bg-primary text-white border shadow-sm rounded-pill">{{ $stats['review_rfc'] ?? 0 }}</span>
            </h6>
            <a href="{{ route('admin.customers.index', ['stage' => 'RFC Done']) }}" class="kanban-card border-primary border-opacity-25 border-start-3" style="border-left-width: 4px; border-left-color: #2563eb;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-2">Review</span>
                    <i class="fas fa-check-double text-primary"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">RFC Testing Completed</h6>
                <p class="text-muted small mb-3">Awaiting final conversion approval.</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="progress w-50" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small fw-bold text-muted">85%</span>
                </div>
            </a>
        </div>

        <!-- DONE -->
        <div class="kanban-column">
            <h6 class="d-flex justify-content-between align-items-center">
                DONE <span class="badge bg-success text-white border shadow-sm rounded-pill">{{ $stats['done_conversion'] ?? 0 }}</span>
            </h6>
            <a href="{{ route('admin.customers.index', ['stage' => 'Converted']) }}" class="kanban-card border-success border-opacity-25 border-start-3" style="border-left-width: 4px; border-left-color: #10b981;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill mb-2">Completed</span>
                    <i class="fas fa-fire text-success"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Conversions Live</h6>
                <p class="text-muted small mb-3">Fully active and live customers.</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="progress w-50" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small fw-bold text-success"><i class="fas fa-check"></i> 100%</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-history text-secondary me-2"></i>Recent Activity</span>
                </div>
                <div class="card-body px-4 py-4">
                    <div class="activity-timeline">
                        
                        @if(isset($recentCustomers))
                        @forelse($recentCustomers->take(3) as $customer)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success"><i class="fas fa-user-plus"></i></div>
                            <div class="ms-2">
                                <p class="mb-1 fw-bold text-dark">New Customer Registered: <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none text-primary">{{ $customer->company_name ?? $customer->name }}</a></p>
                                <p class="text-muted small mb-0"><i class="far fa-clock me-1"></i> {{ $customer->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        @endforelse
                        @endif

                        @if(isset($recentInvoices))
                        @forelse($recentInvoices->take(2) as $invoice)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="ms-2">
                                <p class="mb-1 fw-bold text-dark">Invoice #{{ $invoice->invoice_number }} created for <span class="text-primary">{{ $invoice->customer->name ?? 'Unknown' }}</span></p>
                                <p class="text-muted small mb-0"><i class="far fa-clock me-1"></i> {{ $invoice->created_at->diffForHumans() }} <span class="mx-2">|</span> Amount: ₹{{ number_format($invoice->total, 2) }}</p>
                            </div>
                        </div>
                        @empty
                        @endforelse
                        @endif

                        @if(isset($recentTickets))
                        @forelse($recentTickets->take(2) as $ticket)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-warning"><i class="fas fa-ticket-alt"></i></div>
                            <div class="ms-2">
                                <p class="mb-1 fw-bold text-dark">Ticket <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="text-primary">#{{ $ticket->ticket_number }}</a> created: "{{ Str::limit($ticket->subject, 40) }}"</p>
                                <p class="text-muted small mb-0"><i class="far fa-clock me-1"></i> {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        @endforelse
                        @endif
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Animated Counters
    const counters = document.querySelectorAll('.counter');
    const speed = 200; 

    counters.forEach(counter => {
        const updateCount = () => {
            const targetText = counter.getAttribute('data-target') || "0";
            const isCurrency = counter.classList.contains('is-currency');
            const target = +targetText.replace(/[^0-9.]/g, '');
            const count = +counter.innerText.replace(/[^0-9.]/g, '');

            const inc = target / speed;

            if (count < target) {
                let newVal = Math.ceil(count + inc);
                if(newVal > target) newVal = target;
                
                if (isCurrency) {
                    counter.innerText = '₹' + newVal.toLocaleString('en-IN', {minimumFractionDigits: 2});
                } else {
                    counter.innerText = newVal.toLocaleString('en-IN');
                }
                setTimeout(updateCount, 10);
            } else {
                if (isCurrency) {
                    counter.innerText = '₹' + target.toLocaleString('en-IN', {minimumFractionDigits: 2});
                } else {
                    counter.innerText = target.toLocaleString('en-IN');
                }
            }
        };
        counter.innerText = '0';
        updateCount();
    });

    // Helper to generate sparklines
    const createSparkline = (canvasId, color, data) => {
        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1','2','3','4','5','6','7'],
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                layout: { padding: 0 }
            }
        });
    };

    // Dummy data for sparklines to make them look active
    createSparkline('spark1', '#2563eb', [12, 19, 15, 25, 22, 30, 28]);
    createSparkline('spark2', '#ef4444', [5, 15, 10, 20, 25, 35, 40]);
    createSparkline('spark3', '#10b981', [20, 15, 30, 25, 40, 35, 50]);
    createSparkline('spark4', '#f59e0b', [30, 25, 20, 15, 10, 5, 2]);

    // MAIN REVENUE CHART
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const monthlyRevenue = {!! json_encode($monthlyRevenue ?? []) !!};
    
    let revLabels = monthlyRevenue.map(item => item.month);
    let revData = monthlyRevenue.map(item => item.revenue);

    if (revLabels.length === 0) {
        revLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        revData = [12000, 19000, 15000, 25000, 22000, 30000]; // Visually pleasing default if empty
    }

    // Gradient
    let gradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revLabels,
            datasets: [{
                label: 'Revenue (₹)',
                data: revData,
                borderColor: '#2563eb',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { callback: function(value) { return '₹' + value; } }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });

    // CUSTOMER GROWTH CHART
    const customerCtx = document.getElementById('customerChart').getContext('2d');
    const monthlyCustomers = {!! json_encode($monthlyCustomers ?? []) !!};
    
    let custLabels = monthlyCustomers.map(item => item.month);
    let custData = monthlyCustomers.map(item => item.count);

    if (custLabels.length === 0) {
        custLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        custData = [10, 15, 25, 20, 35, 45];
    }

    new Chart(customerCtx, {
        type: 'bar',
        data: {
            labels: custLabels,
            datasets: [{
                label: 'New Customers',
                data: custData,
                backgroundColor: '#10b981',
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });
});
</script>
@endpush
