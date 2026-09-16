<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_employees' => User::where('status', 'active')->count(),
            'total_customers' => Customer::count(),
            'total_revenue' => Invoice::where('payment_status', 'paid')->sum('total'),
            'pending_invoices' => Invoice::whereIn('payment_status', ['unpaid', 'partial'])->count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'progress'])->count(),
            'active_tasks' => Task::whereIn('status', ['todo', 'progress'])->count(),
            'low_stock_products' => Product::lowStock()->count(),
            'total_leads' => Appointment::whereNotIn('appointment_status', ['converted', 'denied'])->count(),

            // Operational Kanban Metrics
            'todo_registrations' => Customer::where('customer_stage', 'Registered')->orWhereNull('customer_stage')->whereNull('lmc_date')->count(),
            'inprogress_lmc'     => Customer::whereNotNull('lmc_date')->whereNull('rfc_date')->count(),
            'review_rfc'         => Customer::whereNotNull('rfc_date')->whereNull('conversion_date')->count(),
            'done_conversion'    => Customer::whereNotNull('conversion_date')->count(),
        ];

        // Monthly Revenue Chart Data
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite' ? 'strftime("%Y-%m", invoice_date)' : 'DATE_FORMAT(invoice_date, "%Y-%m")';
        $monthExprCust = $driver === 'sqlite' ? 'strftime("%Y-%m", created_at)' : 'DATE_FORMAT(created_at, "%Y-%m")';

        $monthlyRevenue = Invoice::where('payment_status', 'paid')
            ->where('invoice_date', '>=', now()->subMonths(6))
            ->selectRaw("$monthExpr as month, SUM(total) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyCustomers = Customer::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("$monthExprCust as month, COUNT(id) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent Activities
        $recentInvoices = Invoice::with('customer')->latest()->take(5)->get();
        $recentTickets = Ticket::with('customer', 'assignee')->latest()->take(5)->get();
        $recentCustomers = Customer::with('assignee')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'monthlyRevenue', 'monthlyCustomers', 'recentInvoices', 'recentTickets', 'recentCustomers'));
    }
}
