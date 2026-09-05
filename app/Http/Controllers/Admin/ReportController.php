<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function gstSummary(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        $invoices = Invoice::whereBetween('invoice_date', [$fromDate, $toDate])
            ->with('customer')
            ->get();

        $summary = [
            'total_cgst' => $invoices->sum('cgst'),
            'total_sgst' => $invoices->sum('sgst'),
            'total_igst' => $invoices->sum('igst'),
            'total_tax' => $invoices->sum(function ($inv) {
                return $inv->cgst + $inv->sgst + $inv->igst;
            }),
            'total_revenue' => $invoices->sum('total'),
        ];

        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('admin.reports.gst-pdf', compact('invoices', 'summary', 'fromDate', 'toDate'));
                return $pdf->download('gst-summary-' . date('Y-m-d') . '.pdf');
            }
        }

        return view('admin.reports.gst-summary', compact('invoices', 'summary', 'fromDate', 'toDate'));
    }

    public function salesReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));
        $customerId = $request->input('customer_id');

        $query = Invoice::whereBetween('invoice_date', [$fromDate, $toDate])
            ->with('customer', 'items.product');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $invoices = $query->get();
        $customers = Customer::all();

        $summary = [
            'total_invoices' => $invoices->count(),
            'total_revenue' => $invoices->sum('total'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'pending_amount' => $invoices->sum(function ($inv) {
                return $inv->total - $inv->paid_amount;
            }),
        ];

        return view('admin.reports.sales-report', compact('invoices', 'summary', 'customers', 'fromDate', 'toDate', 'customerId'));
    }

    public function inventoryReport()
    {
        $products = Product::with('stockTransactions')->get();
        $lowStockProducts = Product::lowStock()->get();

        $summary = [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($p) {
                return $p->current_stock * $p->price;
            }),
            'low_stock_items' => $lowStockProducts->count(),
            'out_of_stock' => $products->where('current_stock', 0)->count(),
        ];

        return view('admin.reports.inventory-report', compact('products', 'summary', 'lowStockProducts'));
    }

    public function customerReport()
    {
        $customers = Customer::withCount('invoices')
            ->with(['invoices' => function ($query) {
                $query->select('customer_id', DB::raw('SUM(total) as total_spent'))
                    ->groupBy('customer_id');
            }])
            ->get();

        return view('admin.reports.customer-report', compact('customers'));
    }

    public function balanceSheet()
    {
        $assets = Account::where('account_type', 'asset')->get();
        $liabilities = Account::where('account_type', 'liability')->get();
        $equity = Account::where('account_type', 'equity')->get();

        $summary = [
            'total_assets' => $assets->sum('current_balance'),
            'total_liabilities' => $liabilities->sum('current_balance'),
            'total_equity' => $equity->sum('current_balance'),
        ];

        return view('admin.reports.balance-sheet', compact('assets', 'liabilities', 'equity', 'summary'));
    }

    public function profitLoss(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        $income = Account::where('account_type', 'income')->get();
        $expenses = Account::where('account_type', 'expense')->get();

        $summary = [
            'total_income' => $income->sum('current_balance'),
            'total_expenses' => $expenses->sum('current_balance'),
            'net_profit' => $income->sum('current_balance') - $expenses->sum('current_balance'),
        ];

        return view('admin.reports.profit-loss', compact('income', 'expenses', 'summary', 'fromDate', 'toDate'));
    }

    public function trialBalance()
    {
        $accounts = Account::all();
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            if (in_array($account->account_type, ['asset', 'expense'])) {
                $account->debit = $account->current_balance;
                $account->credit = 0;
                $totalDebit += $account->current_balance;
            } else {
                $account->debit = 0;
                $account->credit = $account->current_balance;
                $totalCredit += $account->current_balance;
            }
        }

        return view('admin.reports.trial-balance', compact('accounts', 'totalDebit', 'totalCredit'));
    }
}
