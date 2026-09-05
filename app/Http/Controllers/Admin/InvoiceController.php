<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with('customer')->latest();
        
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        $invoices = $query->get();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        $clients = \App\Models\Client::where('is_active', true)->get();
        $products = Product::all();
        $invoiceNo = $this->invoiceService->generateInvoiceNumber();
        
        return view('admin.invoices.create', compact('customers', 'clients', 'products', 'invoiceNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'                => 'required|exists:clients,id',
            'customer_name'            => 'nullable|string|max:255',
            'client_po_id'             => 'nullable|exists:client_pos,id',
            'invoice_date'             => 'required|date',
            'due_date'                 => 'required|date|after_or_equal:invoice_date',
            'invoice_type'             => 'required|in:tax_invoice,without_gst,proforma',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_name'     => 'required|string|max:255',
            'items.*.description'      => 'nullable|string',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit'             => 'nullable|string|max:50',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'include_payment_info'     => 'nullable|boolean',
            'bank_name'                => 'nullable|string|max:255',
            'bank_account_name'        => 'nullable|string|max:255',
            'bank_account_number'      => 'nullable|string|max:50',
            'bank_ifsc'                => 'nullable|string|max:20',
            'upi_id'                   => 'nullable|string|max:100',
        ]);

        $customerId = null;
        if (!empty($validated['customer_name'])) {
            $customer = Customer::firstOrCreate(
                ['name' => $validated['customer_name']],
                ['phone' => '0000000000', 'source' => 'manual']
            );
            $customerId = $customer->id;
        }

        $processedItems = [];
        foreach ($validated['items'] as $item) {
            $product = Product::where('name', $item['product_name'])->first();
            $processedItems[] = [
                'product_id' => $product ? $product->id : null,
                'description' => $item['description'] ?: $item['product_name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
            ];
        }

        $invoiceData = [
            'invoice_no' => $this->invoiceService->generateInvoiceNumber(),
            'client_id' => $validated['client_id'],
            'customer_id' => $customerId,
            'client_po_id' => $validated['client_po_id'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'invoice_type' => $validated['invoice_type'],
            'notes' => $validated['notes'],
            'include_payment_info' => $validated['include_payment_info'] ?? false,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            'upi_id' => $validated['upi_id'] ?? null,
            'created_by' => auth()->id(),
        ];

        $invoice = $this->invoiceService->createInvoice($invoiceData, $processedItems);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice created successfully');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product', 'payments', 'shipment');
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $customers = Customer::all();
        $products = Product::all();
        $invoice->load('items');
        
        return view('admin.invoices.edit', compact('invoice', 'customers', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
        ]);

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice updated successfully');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load('customer', 'client', 'items.product');
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $customerName = $invoice->customer->name ?? ($invoice->client->name ?? 'Customer');
        $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', trim($customerName));
        $filename = $safeName . '-' . $invoice->invoice_no . '.pdf';

        return $pdf->download($filename);
    }

    public function publicDownloadPdf($invoice_no)
    {
        $invoice = Invoice::where('invoice_no', $invoice_no)->firstOrFail();
        $invoice->load('customer', 'client', 'items.product');
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $customerName = $invoice->customer->name ?? ($invoice->client->name ?? 'Customer');
        $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', trim($customerName));
        $filename = $safeName . '-' . $invoice->invoice_no . '.pdf';

        return $pdf->stream($filename);
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'required|in:cash,neft,upi,cheque,bank_transfer',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        Payment::create([
            'invoice_id'     => $invoice->id,
            'payment_date'   => $validated['payment_date'],
            'amount'         => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_no'   => $validated['reference_no'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        // Update paid_amount and status
        $invoice->refresh();
        $totalPaid = $invoice->payments()->sum('amount');
        $invoice->paid_amount = $totalPaid;

        if ($totalPaid >= $invoice->total) {
            $invoice->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $invoice->payment_status = 'partial';
        } else {
            $invoice->payment_status = 'unpaid';
        }
        $invoice->save();

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully.');
    }

    public function bulkMarkPaid(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
        ]);

        $count = 0;
        DB::transaction(function () use ($validated, &$count) {
            foreach ($validated['invoice_ids'] as $id) {
                $invoice = Invoice::find($id);
                $pending = $invoice->balance_due;
                if ($pending > 0) {
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'payment_date' => $validated['payment_date'],
                        'amount' => $pending,
                        'payment_method' => $validated['payment_method'],
                        'reference_no' => $validated['reference_no'],
                        'created_by' => auth()->id(),
                    ]);

                    $invoice->paid_amount = $invoice->total;
                    $invoice->payment_status = 'paid';
                    $invoice->save();
                    $count++;
                }
            }
        });

        return redirect()->route('admin.invoices.index')
            ->with('success', $count . ' invoices marked as fully paid successfully.');
    }
}
