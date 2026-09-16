<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Employee;
// use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->hasRole('admin') 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('employee.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Public GST Bill PDF download route (accessible without login)
Route::get('/bill/{invoice_no}', [Admin\InvoiceController::class, 'publicDownloadPdf'])->name('public.invoices.pdf');

// Authenticated Routes
Route::get('/clear-cache', function() {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return redirect('/admin/dashboard')->with('success', 'Cache cleared successfully!');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/logout', [LogoutController::class, 'logout'])->name('logout.get');


    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Employee Management
        Route::resource('employees', Admin\EmployeeController::class);
        
        // Customer Management
        Route::get('/customers/export', [Admin\CustomerController::class, 'export'])->name('customers.export');
        Route::post('/customers/import', [Admin\CustomerController::class, 'import'])->name('customers.import');
        Route::post('/customers/assignment-settings', [Admin\CustomerController::class, 'updateAssignmentSettings'])->name('customers.assignment-settings');
        Route::post('/customers/store-ajax', [Admin\CustomerController::class, 'storeAjax'])->name('customers.store-ajax');
        Route::resource('customers', Admin\CustomerController::class);
        
        // Societies Master List
        Route::get('societies/customers', [Admin\SocietyController::class, 'getCustomers'])->name('societies.customers');
        Route::resource('societies', Admin\SocietyController::class);
        
        // Appointment Management
        Route::post('/appointments/bulk-schedule', [Admin\AppointmentController::class, 'bulkSchedule'])->name('appointments.bulk-schedule');
        Route::post('/appointments/{appointment}/deny', [Admin\AppointmentController::class, 'deny'])->name('appointments.deny');
        Route::post('/appointments/{appointment}/reopen', [Admin\AppointmentController::class, 'reopen'])->name('appointments.reopen');
        Route::post('/appointments/{appointment}/quick-reschedule', [Admin\AppointmentController::class, 'quickReschedule'])->name('appointments.quick-reschedule');
        Route::resource('appointments', Admin\AppointmentController::class);

        // Master Data: Burner Types, Meter Types, Contractors, Suppliers, Clients
        Route::resource('burner-types', Admin\BurnerTypeController::class);
        Route::resource('meter-types', Admin\MeterTypeController::class);
        Route::resource('contractors', Admin\ContractorController::class);
        Route::resource('suppliers', Admin\SupplierController::class);
        Route::post('/clients/{client}/toggle', [Admin\ClientController::class, 'toggleStatus'])->name('clients.toggle');
        Route::resource('clients', Admin\ClientController::class);
        
        // Product Management
        Route::resource('products', Admin\ProductController::class);
        
        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [Admin\InventoryController::class, 'index'])->name('index');
            Route::get('/logs', [Admin\InventoryController::class, 'logs'])->name('logs');
            Route::post('/logs/store', [Admin\InventoryController::class, 'storeLog'])->name('logs.store');
            Route::put('/logs/{log}', [Admin\InventoryController::class, 'updateLog'])->name('logs.update');
            Route::post('/logs/bulk', [Admin\InventoryController::class, 'bulkStoreLog'])->name('logs.bulk');
            Route::get('/reconciliation', [Admin\InventoryController::class, 'reconciliation'])->name('reconciliation');
            Route::get('/stock-transactions', [Admin\InventoryController::class, 'stockTransactions'])->name('transactions');
            Route::post('/stock-in', [Admin\InventoryController::class, 'stockIn'])->name('stock-in');
            Route::post('/stock-out', [Admin\InventoryController::class, 'stockOut'])->name('stock-out');
            Route::post('/approve-transaction/{transaction}', [Admin\InventoryController::class, 'approveTransaction'])->name('approve-transaction');
            Route::delete('/delete-transaction/{transaction}', [Admin\InventoryController::class, 'deleteTransaction'])->name('delete-transaction');
        });
        
        // Quotation Management
        Route::resource('quotations', Admin\QuotationController::class);
        Route::get('/quotations/{quotation}/pdf', [Admin\QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
        Route::post('/quotations/{quotation}/convert-to-invoice', [Admin\QuotationController::class, 'convertToInvoice'])->name('quotations.convert-to-invoice');
        
        // Invoice Management
        Route::resource('invoices', Admin\InvoiceController::class);
        Route::get('/invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/payment', [Admin\InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
        Route::post('/invoices/bulk-mark-paid', [Admin\InvoiceController::class, 'bulkMarkPaid'])->name('invoices.bulk-mark-paid');

        // Client POs & Progress tracking
        Route::resource('client-pos', Admin\ClientPoController::class);
        Route::post('client-pos/{client_po}/progress', [Admin\ClientPoController::class, 'addProgress'])->name('client-pos.progress');

        // Vendor POs & Invoices
        Route::post('vendor-pos/ocr-parse-bill', [Admin\VendorPoController::class, 'ocrParseBill'])->name('vendor-pos.ocr-parse-bill');
        Route::get('vendor-pos/{vendor_po}/pdf', [Admin\VendorPoController::class, 'downloadPdf'])->name('vendor-pos.pdf');
        Route::resource('vendor-pos', Admin\VendorPoController::class);
        Route::resource('vendor-invoices', Admin\VendorInvoiceController::class);
        Route::post('vendor-invoices/{vendor_invoice}/approve', [Admin\VendorInvoiceController::class, 'approve'])->name('vendor-invoices.approve');
        Route::post('vendor-invoices/{vendor_invoice}/payment', [Admin\VendorInvoiceController::class, 'recordPayment'])->name('vendor-invoices.record-payment');

        
        // Accounting
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [Admin\AccountController::class, 'index'])->name('index');
            Route::get('/create', [Admin\AccountController::class, 'create'])->name('create');
            Route::post('/', [Admin\AccountController::class, 'store'])->name('store');
            
            // Journal Entry routes (must come before {account} routes)
            Route::get('/journal-entries', [Admin\JournalEntryController::class, 'index'])->name('journal.index');
            Route::get('/journal-entries/create', [Admin\JournalEntryController::class, 'create'])->name('journal.create');
            Route::post('/journal-entries', [Admin\JournalEntryController::class, 'store'])->name('journal.store');
            Route::get('/journal-entries/{journalEntry}', [Admin\JournalEntryController::class, 'show'])->name('journal.show');
            Route::get('/journal-entries/{journalEntry}/edit', [Admin\JournalEntryController::class, 'edit'])->name('journal.edit');
            Route::put('/journal-entries/{journalEntry}', [Admin\JournalEntryController::class, 'update'])->name('journal.update');
            Route::delete('/journal-entries/{journalEntry}', [Admin\JournalEntryController::class, 'destroy'])->name('journal.destroy');
            
            // Report routes (must come before {account} routes)
            Route::get('/balance-sheet', [Admin\ReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/profit-loss', [Admin\ReportController::class, 'profitLoss'])->name('profit-loss');
            
            // Account resource routes (must come last)
            Route::get('/{account}', [Admin\AccountController::class, 'show'])->name('show');
            Route::get('/{account}/edit', [Admin\AccountController::class, 'edit'])->name('edit');
            Route::put('/{account}', [Admin\AccountController::class, 'update'])->name('update');
            Route::delete('/{account}', [Admin\AccountController::class, 'destroy'])->name('destroy');
        });
        
        // Task Management
        // Route::resource('expenses', ExpenseController::class);

        // API Routes for AJAX
        Route::get('/api/verify-gstin/{gstin}', [ApiController::class, 'verifyGstin'])->name('api.verify-gstin');
        
        Route::resource('tasks', Admin\TaskController::class);
        Route::post('/tasks/{task}/status', [Admin\TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/quick-assign', [Admin\TaskController::class, 'quickAssign'])->name('tasks.quick-assign');
        Route::post('/tasks/{task}/toggle-checklist', [Admin\TaskController::class, 'toggleChecklist'])->name('tasks.toggle-checklist');
        Route::post('/tasks/{task}/comment', [Admin\TaskController::class, 'addComment'])->name('tasks.add-comment');
        
        // Ticket Management
        Route::resource('tickets', Admin\TicketController::class);
        Route::post('/tickets/{ticket}/comment', [Admin\TicketController::class, 'addComment'])->name('tickets.add-comment');
        
        // Shipment Management
        Route::resource('shipments', Admin\ShipmentController::class);
        
        // Warehouse Management
        Route::resource('warehouses', Admin\WarehouseController::class);

        // Branch Management
        Route::resource('branches', Admin\BranchController::class);

        // Credit / Debit Notes Management
        Route::resource('notes', Admin\CreditDebitNoteController::class);
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [Admin\ReportController::class, 'index'])->name('index');
            Route::get('/gst-summary', [Admin\ReportController::class, 'gstSummary'])->name('gst-summary');
            Route::get('/sales-report', [Admin\ReportController::class, 'salesReport'])->name('sales-report');
            Route::get('/inventory-report', [Admin\ReportController::class, 'inventoryReport'])->name('inventory-report');
            Route::get('/customer-report', [Admin\ReportController::class, 'customerReport'])->name('customer-report');
            Route::get('/trial-balance', [Admin\ReportController::class, 'trialBalance'])->name('trial-balance');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [Admin\SettingsController::class, 'index'])->name('index');
            Route::post('/update', [Admin\SettingsController::class, 'update'])->name('update');
            Route::get('/backup', [Admin\BackupController::class, 'index'])->name('backup');
            Route::get('/backup/download', [Admin\BackupController::class, 'downloadBackup'])->name('backup.download');
            Route::post('/backup/restore', [Admin\BackupController::class, 'restoreBackup'])->name('backup.restore');
        });
        
        // WhatsApp Integration
        Route::post('/whatsapp/webhook', [Admin\WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');
        Route::get('/whatsapp/messages', [Admin\WhatsAppController::class, 'messages'])->name('whatsapp.messages');

        // ─── CRM Chatbot (Admin) ─────────────────────────────────────────────
        Route::post('/chatbot/search', [Admin\ChatbotController::class, 'search'])->name('chatbot.search');
        Route::post('/chatbot/profile', [Admin\ChatbotController::class, 'getProfile'])->name('chatbot.profile');
        Route::post('/chatbot/clear', [Admin\ChatbotController::class, 'clearContext'])->name('chatbot.clear');
    });




    
    // Employee Routes
    Route::prefix('employee')->name('employee.')->middleware('role:employee|admin')->group(function () {
        Route::get('/dashboard', [Employee\DashboardController::class, 'index'])->name('dashboard');
        
        // Employee Tasks
        Route::resource('tasks', Employee\TaskController::class)->only(['index', 'show', 'update']);
        Route::post('/tasks/{task}/status', [Employee\TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/comment', [Employee\TaskController::class, 'addComment'])->name('tasks.add-comment');
        
        // Employee Tickets
        Route::resource('tickets', Employee\TicketController::class)->only(['index', 'show', 'update']);
        Route::post('/tickets/{ticket}/comment', [Employee\TicketController::class, 'addComment'])->name('tickets.add-comment');
        
        // Employee Leads
        Route::resource('leads', Employee\LeadController::class)->only(['index', 'show', 'update']);

        // Employee Customers
        Route::resource('customers', Employee\CustomerController::class)->only(['index', 'show', 'edit', 'update']);

        // Employee Appointments (Calendar & Field Response)
        Route::get('/appointments', [Employee\AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/appointments/{appointment}/response', [Employee\AppointmentController::class, 'submitResponse'])->name('appointments.response');

        // ─── CRM Chatbot (Employee — same controller, restricted results) ───
        Route::post('/chatbot/search', [Admin\ChatbotController::class, 'search'])->name('chatbot.search');
        Route::post('/chatbot/profile', [Admin\ChatbotController::class, 'getProfile'])->name('chatbot.profile');
        Route::post('/chatbot/clear', [Admin\ChatbotController::class, 'clearContext'])->name('chatbot.clear');
    });
});

Route::get('/force-login', function() {
    $admin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@hisabmittra.com'],
        [
            'name' => 'Admin User',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'phone' => '9876543210',
            'department' => 'Management',
            'status' => 'active',
        ]
    );
    
    $admin->password = \Illuminate\Support\Facades\Hash::make('password');
    $admin->save();
    
    // Crucial: assign the admin role so they don't get a 403 error
    if (! $admin->hasRole('admin')) {
        $admin->assignRole('admin');
    }

    \Illuminate\Support\Facades\Auth::login($admin);
    return '<h3>Login successful!</h3><p>You are now logged in as Admin.</p><a href="/admin/dashboard">Click here to go to the Dashboard</a>';
});

Route::get('/force-login-employee', function() {
    $employee = \App\Models\User::firstOrCreate(
        ['email' => 'employee@hisabmittra.com'],
        [
            'name' => 'Employee User',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'phone' => '9876543211',
            'department' => 'Sales',
            'status' => 'active',
        ]
    );
    
    $employee->password = \Illuminate\Support\Facades\Hash::make('password');
    $employee->save();
    
    // Assign the employee role
    if (! $employee->hasRole('employee')) {
        $employee->assignRole('employee');
    }

    \Illuminate\Support\Facades\Auth::login($employee);
    return '<h3>Login successful!</h3><p>You are now logged in as Employee.</p><a href="/employee/dashboard">Click here to go to the Employee Dashboard</a>';
});




