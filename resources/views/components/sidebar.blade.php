<div class="sidebar" id="sidebar">
    <div class="logo d-flex align-items-center justify-content-center p-3">
        <a href="{{ auth()->user() && auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('employee.dashboard') }}" class="d-flex align-items-center justify-content-center w-100">
            <img src="{{ asset('MQ logo.png') }}" alt="Metric Qube Logo" style="max-height: 45px; max-width: 100%; object-fit: contain;">
        </a>
    </div>

    <!-- Sidebar Search -->
    <div class="sidebar-search p-3 pb-0">
        <div class="input-group input-group-sm position-relative">
            <span class="input-group-text bg-transparent border-0 text-muted" style="position: absolute; z-index: 10; padding-left: 15px;"><i class="fas fa-search"></i></span>
            <input type="text" id="menuSearch" class="form-control border-0 text-dark rounded-pill" placeholder="Search menu..." style="background: #f1f5f9; padding-left: 35px; box-shadow: none;">
        </div>
    </div>

    <ul class="sidebar-menu" id="sidebarMenu">

        {{-- ===================== ADMIN MENU ===================== --}}
        @hasrole('admin')

        <li class="menu-item-searchable">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>

        {{-- CRM Group --}}
        <li class="sidebar-group">
            <a href="#collapseCRM" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.appointments.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-users-cog"></i> <span>CRM</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.appointments.*') ? 'show' : '' }}" id="collapseCRM" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.customers.index', ['section' => 'registration']) }}" class="{{ request()->routeIs('admin.customers.*') && request('section', 'registration') === 'registration' ? 'active' : '' }}">
                        <i class="fas fa-address-card"></i> <span>Customer Registration</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.customers.index', ['section' => 'technical']) }}" class="{{ request()->routeIs('admin.customers.*') && request('section') === 'technical' ? 'active' : '' }}">
                        <i class="fas fa-tools"></i> <span>LMC & Technical</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.customers.index', ['section' => 'mlc']) }}" class="{{ request()->routeIs('admin.customers.*') && request('section') === 'mlc' ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> <span>MLC (Meter) Details</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.appointments.index') }}" class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i> <span>Appointments</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- BILLING Group --}}
        <li class="sidebar-group">
            <a href="#collapseBilling" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.client-pos.*') || request()->routeIs('admin.vendor-pos.*') || request()->routeIs('admin.vendor-invoices.*') || request()->routeIs('admin.quotations.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('admin.notes.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-file-invoice-dollar"></i> <span>Billing</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.client-pos.*') || request()->routeIs('admin.vendor-pos.*') || request()->routeIs('admin.vendor-invoices.*') || request()->routeIs('admin.quotations.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('admin.notes.*') ? 'show' : '' }}" id="collapseBilling" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.client-pos.index') }}" class="{{ request()->routeIs('admin.client-pos.*') ? 'active' : '' }}">
                        <i class="fas fa-file-contract"></i> <span>Client POs (WO)</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.vendor-pos.index') }}" class="{{ request()->routeIs('admin.vendor-pos.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i> <span>Vendor POs</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.vendor-invoices.index') }}" class="{{ request()->routeIs('admin.vendor-invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> <span>Vendor Invoices</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.quotations.index') }}" class="{{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i> <span>Quotations</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i> <span>Sales Invoices</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.notes.index') }}" class="{{ request()->routeIs('admin.notes.*') ? 'active' : '' }}">
                        <i class="fas fa-calculator"></i> <span>Credit/Debit Notes</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- INVENTORY Group --}}
        <li class="sidebar-group">
            <a href="#collapseInventory" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.inventory.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.warehouses.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-boxes"></i> <span>Inventory</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.inventory.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.warehouses.*') ? 'show' : '' }}" id="collapseInventory" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> <span>Products & Services</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.inventory.logs', ['type' => 'inward']) }}" class="{{ request()->routeIs('admin.inventory.logs') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i> <span>Material Logs (In/Out)</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.inventory.reconciliation') }}" class="{{ request()->routeIs('admin.inventory.reconciliation') ? 'active' : '' }}">
                        <i class="fas fa-balance-scale"></i> <span>FIM Reconciliation</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck-loading"></i> <span>Suppliers</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-handshake"></i> <span>Clients</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.warehouses.index') }}" class="{{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i> <span>Store Masters</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- OPERATIONS Group --}}
        <li class="sidebar-group">
            <a href="#collapseOperations" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.tasks.*') || request()->routeIs('admin.tickets.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-cogs"></i> <span>Operations</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.tasks.*') || request()->routeIs('admin.tickets.*') ? 'show' : '' }}" id="collapseOperations" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.tasks.index') }}" class="{{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i> <span>Tasks</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.tickets.index') }}" class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i> <span>Tickets</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- ACCOUNTS Group --}}
        <li class="sidebar-group">
            <a href="#collapseAccounts" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.accounts.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-university"></i> <span>Accounts</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.accounts.*') ? 'show' : '' }}" id="collapseAccounts" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.accounts.index') }}" class="{{ request()->routeIs('admin.accounts.index') ? 'active' : '' }}">
                        <i class="fas fa-book"></i> <span>Chart of Accounts</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.accounts.journal.index') }}" class="{{ request()->routeIs('admin.accounts.journal.*') ? 'active' : '' }}">
                        <i class="fas fa-journal-whills"></i> <span>Journal Entries</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- ADMIN Group --}}
        <li class="sidebar-group">
            <a href="#collapseAdmin" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.branches.*') || request()->routeIs('admin.societies.*') || request()->routeIs('admin.burner-types.*') || request()->routeIs('admin.meter-types.*') || request()->routeIs('admin.contractors.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.settings.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-user-shield"></i> <span>Admin</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.branches.*') || request()->routeIs('admin.societies.*') || request()->routeIs('admin.burner-types.*') || request()->routeIs('admin.meter-types.*') || request()->routeIs('admin.contractors.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.settings.*') ? 'show' : '' }}" id="collapseAdmin" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> <span>Employees</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                        <i class="fas fa-code-branch"></i> <span>Branches</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.societies.index') }}" class="{{ request()->routeIs('admin.societies.*') ? 'active' : '' }}">
                        <i class="fas fa-city"></i> <span>Societies</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.burner-types.index') }}" class="{{ request()->routeIs('admin.burner-types.*') ? 'active' : '' }}">
                        <i class="fas fa-fire"></i> <span>Burner Types</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.meter-types.index') }}" class="{{ request()->routeIs('admin.meter-types.*') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> <span>Meter Types</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.contractors.index') }}" class="{{ request()->routeIs('admin.contractors.*') ? 'active' : '' }}">
                        <i class="fas fa-hard-hat"></i> <span>Contractors</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> <span>Reports</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('admin.settings.backup') }}" class="{{ request()->routeIs('admin.settings.backup') ? 'active' : '' }}">
                        <i class="fas fa-database"></i> <span>Backup & Restore</span>
                    </a>
                </li>
            </ul>
        </li>

        @endhasrole

        {{-- ===================== EMPLOYEE MENU ===================== --}}
        @hasrole('employee')

        <li class="menu-item-searchable">
            <a href="{{ route('employee.dashboard') }}" class="{{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-group">
            <a href="#collapseMyWork" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ request()->routeIs('employee.tasks.*') || request()->routeIs('employee.tickets.*') || request()->routeIs('employee.appointments.*') || request()->routeIs('employee.customers.*') ? '' : 'collapsed' }}">
                <div><i class="fas fa-briefcase"></i> <span>My Work</span></div>
                <i class="fas fa-chevron-down toggle-icon" style="font-size: 10px; width: auto; margin-right: 0;"></i>
            </a>
            <ul class="collapse sidebar-submenu {{ request()->routeIs('employee.tasks.*') || request()->routeIs('employee.tickets.*') || request()->routeIs('employee.appointments.*') || request()->routeIs('employee.customers.*') ? 'show' : '' }}" id="collapseMyWork" data-bs-parent="#sidebarMenu">
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.tasks.index') }}" class="{{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i> <span>My Tasks</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.tickets.index') }}" class="{{ request()->routeIs('employee.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i> <span>My Tickets</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.appointments.index') }}" class="{{ request()->routeIs('employee.appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i> <span>My Appointments</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.customers.index', ['section' => 'registration']) }}" class="{{ request()->routeIs('employee.customers.*') && request('section', 'registration') === 'registration' ? 'active' : '' }}">
                        <i class="fas fa-address-card"></i> <span>My Customer Registrations</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.customers.index', ['section' => 'technical']) }}" class="{{ request()->routeIs('employee.customers.*') && request('section') === 'technical' ? 'active' : '' }}">
                        <i class="fas fa-tools"></i> <span>My LMC & Technical Updates</span>
                    </a>
                </li>
                <li class="menu-item-searchable">
                    <a href="{{ route('employee.customers.index', ['section' => 'mlc']) }}" class="{{ request()->routeIs('employee.customers.*') && request('section') === 'mlc' ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> <span>My MLC (Meter) Details</span>
                    </a>
                </li>
            </ul>
        </li>

        @endhasrole

    </ul>

    <!-- Sidebar Profile Bottom -->
    @if(auth()->check())
    <div class="sidebar-profile mt-auto">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: bold;">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>
        <div class="overflow-hidden">
            <h6 class="mb-0 text-white text-truncate" style="font-size: 0.9rem;">{{ auth()->user()->name }}</h6>
            <small class="text-white-50 d-block text-truncate" style="font-size: 0.75rem;">{{ auth()->user()->email }}</small>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="ms-auto m-0 p-0">
            @csrf
            <button type="submit" class="btn btn-link text-white-50 p-0 text-decoration-none" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
    @endif
</div>

<!-- Sidebar Menu Filter Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('menuSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const searchableItems = document.querySelectorAll('.menu-item-searchable');
                const groups = document.querySelectorAll('.sidebar-group');
                
                if (term === '') {
                    // Reset everything
                    searchableItems.forEach(item => item.style.display = '');
                    groups.forEach(group => {
                        group.style.display = '';
                    });
                    return;
                }

                // Filter items
                searchableItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(term)) {
                        item.style.display = '';
                        // Open parent collapse
                        const parentUl = item.closest('.sidebar-submenu');
                        if (parentUl && !parentUl.classList.contains('show')) {
                            const bsCollapse = new bootstrap.Collapse(parentUl, { toggle: false });
                            bsCollapse.show();
                        }
                        const parentGroup = item.closest('.sidebar-group');
                        if(parentGroup) parentGroup.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Hide empty groups
                groups.forEach(group => {
                    const visibleItems = group.querySelectorAll('.menu-item-searchable[style=""]');
                    if (visibleItems.length === 0) {
                        group.style.display = 'none';
                    } else {
                        group.style.display = '';
                    }
                });
            });
        }
    });
</script>
