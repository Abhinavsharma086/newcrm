<div class="topbar">
    <div class="topbar-left">
        <button class="btn btn-link d-lg-none p-0 text-dark" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb">
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>
    
    <div class="topbar-right">
        <!-- Quick Actions (Hidden on mobile) -->
        <div class="d-none d-md-flex gap-2 me-3 border-end pe-4">
            <a href="{{ route('admin.customers.create') }}" class="quick-action-btn">
                <i class="fas fa-plus"></i> Add Customer
            </a>
            <a href="{{ route('admin.invoices.index', ['status' => 'draft']) }}" class="quick-action-btn" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                <i class="fas fa-pencil-alt"></i> Draft Invoices
            </a>
            <a href="{{ route('admin.invoices.create') }}" class="quick-action-btn">
                <i class="fas fa-file-invoice"></i> Create Invoice
            </a>
        </div>

        <!-- Help Icon -->
        <a href="#" class="text-muted" title="Help & Support">
            <i class="far fa-question-circle fa-lg"></i>
        </a>

        <!-- Notifications -->
        <div class="dropdown">
            <a href="#" class="position-relative text-muted" data-bs-toggle="dropdown">
                <i class="far fa-bell fa-lg"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 320px; border-radius: 12px;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Notifications</h6>
                    <span class="badge bg-primary rounded-pill">{{ auth()->user()->unreadNotifications->count() }} New</span>
                </div>
                <div class="max-h-300 overflow-auto">
                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                    <a href="#" class="dropdown-item py-3 border-bottom">
                        <small class="text-muted d-block mb-1">{{ $notification->created_at->diffForHumans() }}</small>
                        <p class="mb-0 text-dark text-wrap">{{ $notification->data['message'] ?? 'New notification' }}</p>
                    </a>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="far fa-bell-slash fa-2x mb-2 text-light-gray"></i>
                        <p class="mb-0">No new notifications</p>
                    </div>
                    @endforelse
                </div>
                <a href="#" class="dropdown-item text-center py-2 text-primary fw-bold bg-light" style="border-radius: 0 0 12px 12px;">View All</a>
            </div>
        </div>
        
        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" class="text-muted" title="Settings">
            <i class="fas fa-cog fa-lg"></i>
        </a>

        <!-- User Profile -->
        <div class="dropdown ms-2">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-weight: bold;">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="d-none d-sm-block text-start">
                    <span class="d-block text-dark fw-bold" style="line-height: 1.2;">{{ auth()->user()->name }}</span>
                    <small class="text-muted text-capitalize" style="font-size: 0.75rem;">{{ auth()->user()->roles->first()->name ?? 'User' }}</small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 12px;">
                <li><a class="dropdown-item py-2" href="#"><i class="far fa-user me-2 text-muted"></i> My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
