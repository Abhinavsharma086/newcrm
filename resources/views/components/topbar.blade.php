<div class="topbar">
    <div class="topbar-left">
        <button class="btn btn-link d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>
    
    <div class="topbar-right">
        <!-- Notifications -->
        <div class="dropdown">
            <a href="#" class="position-relative" data-bs-toggle="dropdown">
                <i class="fas fa-bell fa-lg"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                <h6 class="dropdown-header">Notifications</h6>
                <div class="dropdown-divider"></div>
                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                <a href="#" class="dropdown-item">
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    <p class="mb-0">{{ $notification->data['message'] ?? 'New notification' }}</p>
                </a>
                @empty
                <span class="dropdown-item text-muted">No new notifications</span>
                @endforelse
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item text-center">View All</a>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle fa-lg me-2"></i>
                <span>{{ auth()->user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
