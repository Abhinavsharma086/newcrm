<div class="sidebar" id="sidebar">
    <div class="logo d-flex align-items-center justify-content-center p-3">
        <a href="{{ auth()->user() && auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('employee.dashboard') }}" class="d-flex align-items-center justify-content-center w-100">
            <img src="{{ asset('MQ logo.png') }}" alt="Metric Qube Logo" style="max-height: 25px; width: auto; object-fit: contain;">
        </a>
    </div>

    <ul class="sidebar-menu">

        {{-- ===================== ADMIN MENU ===================== --}}
        @hasrole('admin')

        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        {{-- CRM --}}
        <li class="sidebar-section">CRM</li>

        <li>
            <a href="{{ route('admin.customers.index', ['section' => 'registration']) }}"
               class="{{ request()->routeIs('admin.customers.*') && request('section', 'registration') === 'registration' ? 'active' : '' }}">
                <i class="fas fa-address-card"></i> Customer Registration
            </a>
        </li>

        <li>
            <a href="{{ route('admin.customers.index', ['section' => 'technical']) }}"
               class="{{ request()->routeIs('admin.customers.*') && request('section') === 'technical' ? 'active' : '' }}">
                <i class="fas fa-tools"></i> LMC & Technical Details
            </a>
        </li>

        <li>
            <a href="{{ route('admin.customers.index', ['section' => 'mlc']) }}"
               class="{{ request()->routeIs('admin.customers.*') && request('section') === 'mlc' ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> MLC (Meter) Details
            </a>
        </li>

        <li>
            <a href="{{ route('admin.appointments.index') }}"
               class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> Confirmed Appointments
            </a>
        </li>

        {{-- BILLING --}}
        <li class="sidebar-section">Billing</li>

        <li>
            <a href="{{ route('admin.client-pos.index') }}"
               class="{{ request()->routeIs('admin.client-pos.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract"></i> Client POs (WO)
            </a>
        </li>

        <li>
            <a href="{{ route('admin.vendor-pos.index') }}"
               class="{{ request()->routeIs('admin.vendor-pos.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> Vendor POs
            </a>
        </li>

        <li>
            <a href="{{ route('admin.vendor-invoices.index') }}"
               class="{{ request()->routeIs('admin.vendor-invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i> Vendor Invoices
            </a>
        </li>

        <li>
            <a href="{{ route('admin.quotations.index') }}"
               class="{{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Quotations
            </a>
        </li>

        <li>
            <a href="{{ route('admin.invoices.index') }}"
               class="{{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Sales Invoices
            </a>
        </li>

        <li>
            <a href="{{ route('admin.notes.index') }}"
               class="{{ request()->routeIs('admin.notes.*') ? 'active' : '' }}">
                <i class="fas fa-calculator"></i> Credit/Debit Notes
            </a>
        </li>

        {{-- INVENTORY --}}
        <li class="sidebar-section">Inventory</li>

        <li>
            <a href="{{ route('admin.products.index') }}"
               class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i> Products & Services
            </a>
        </li>

        <li>
            <a href="{{ route('admin.inventory.logs', ['type' => 'inward']) }}"
               class="{{ request()->routeIs('admin.inventory.logs') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i> Material Logs (In/Out)
            </a>
        </li>

        <li>
            <a href="{{ route('admin.inventory.reconciliation') }}"
               class="{{ request()->routeIs('admin.inventory.reconciliation') ? 'active' : '' }}">
                <i class="fas fa-balance-scale"></i> FIM Reconciliation
            </a>
        </li>

        <li>
            <a href="{{ route('admin.suppliers.index') }}"
               class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                <i class="fas fa-truck-loading"></i> Suppliers
            </a>
        </li>

        <li>
            <a href="{{ route('admin.clients.index') }}"
               class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <i class="fas fa-handshake"></i> Clients
            </a>
        </li>

        <li>
            <a href="{{ route('admin.warehouses.index') }}"
               class="{{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i> Store Masters
            </a>
        </li>

        {{-- OPERATIONS --}}
        <li class="sidebar-section">Operations</li>

        <li>
            <a href="{{ route('admin.tasks.index') }}"
               class="{{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> Tasks
            </a>
        </li>

        <li>
            <a href="{{ route('admin.tickets.index') }}"
               class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> Tickets
            </a>
        </li>

        {{-- ACCOUNTS --}}
        <li class="sidebar-section">Accounts</li>

        <li>
            <a href="{{ route('admin.accounts.index') }}"
               class="{{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                <i class="fas fa-book"></i> Chart of Accounts
            </a>
        </li>

        <li>
            <a href="{{ route('admin.accounts.journal.index') }}"
               class="{{ request()->routeIs('admin.accounts.journal.*') ? 'active' : '' }}">
                <i class="fas fa-journal-whills"></i> Journal Entries
            </a>
        </li>

        {{-- ADMIN --}}
        <li class="sidebar-section">Admin</li>

        <li>
            <a href="{{ route('admin.employees.index') }}"
               class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Employees
            </a>
        </li>

        <li>
            <a href="{{ route('admin.branches.index') }}"
               class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                <i class="fas fa-code-branch"></i> Branches
            </a>
        </li>

        <li>
            <a href="{{ route('admin.societies.index') }}"
               class="{{ request()->routeIs('admin.societies.*') ? 'active' : '' }}">
                <i class="fas fa-city"></i> Societies
            </a>
        </li>

        <li>
            <a href="{{ route('admin.burner-types.index') }}"
               class="{{ request()->routeIs('admin.burner-types.*') ? 'active' : '' }}">
                <i class="fas fa-fire"></i> Burner Types
            </a>
        </li>

        <li>
            <a href="{{ route('admin.meter-types.index') }}"
               class="{{ request()->routeIs('admin.meter-types.*') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Meter Types
            </a>
        </li>

        <li>
            <a href="{{ route('admin.contractors.index') }}"
               class="{{ request()->routeIs('admin.contractors.*') ? 'active' : '' }}">
                <i class="fas fa-hard-hat"></i> Contractors
            </a>
        </li>

        <li>
            <a href="{{ route('admin.reports.index') }}"
               class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>

        <li>
            <a href="{{ route('admin.settings.index') }}"
               class="{{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>

        <li>
            <a href="{{ route('admin.settings.backup') }}"
               class="{{ request()->routeIs('admin.settings.backup') ? 'active' : '' }}">
                <i class="fas fa-database"></i> Backup & Restore
            </a>
        </li>

        @endhasrole

        {{-- ===================== EMPLOYEE MENU ===================== --}}
        @hasrole('employee')

        <li>
            <a href="{{ route('employee.dashboard') }}"
               class="{{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <li class="sidebar-section">My Work</li>



        <li>
            <a href="{{ route('employee.tasks.index') }}"
               class="{{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> My Tasks
            </a>
        </li>

        <li>
            <a href="{{ route('employee.tickets.index') }}"
               class="{{ request()->routeIs('employee.tickets.*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> My Tickets
            </a>
        </li>

        <li>
            <a href="{{ route('employee.appointments.index') }}"
               class="{{ request()->routeIs('employee.appointments.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> My Appointments
            </a>
        </li>

        <li>
            <a href="{{ route('employee.customers.index', ['section' => 'registration']) }}"
               class="{{ request()->routeIs('employee.customers.*') && request('section', 'registration') === 'registration' ? 'active' : '' }}">
                <i class="fas fa-address-card"></i> My Customer Registrations
            </a>
        </li>

        <li>
            <a href="{{ route('employee.customers.index', ['section' => 'technical']) }}"
               class="{{ request()->routeIs('employee.customers.*') && request('section') === 'technical' ? 'active' : '' }}">
                <i class="fas fa-tools"></i> My LMC & Technical Updates
            </a>
        </li>

        <li>
            <a href="{{ route('employee.customers.index', ['section' => 'mlc']) }}"
               class="{{ request()->routeIs('employee.customers.*') && request('section') === 'mlc' ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> My MLC (Meter) Details
            </a>
        </li>

        @endhasrole

    </ul>
</div>
