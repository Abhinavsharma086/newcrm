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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->get();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all()->unique('name');
        $billers = Customer::all()->unique('name'); // Used for Biller/GST Holder dropdown
        $clients = \App\Models\Client::where('is_active', true)->get()->unique('name');
        $products = Product::all()->unique('name');
        $invoiceNo = $this->invoiceService->generateInvoiceNumber();
        
        return view('admin.invoices.create', compact('customers', 'billers', 'clients', 'products', 'invoiceNo'));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('action') === 'draft';
        
        // Filter out empty rows (where product_name is empty) before validation
        if ($request->has('items')) {
            $items = array_filter($request->input('items'), function($item) {
                return !empty($item['product_name']);
            });
            
            // Default missing quantity and unit price
            foreach ($items as &$item) {
                if (!isset($item['quantity']) || $item['quantity'] === '') {
                    $item['quantity'] = 1;
                }
                if (!isset($item['unit_price']) || $item['unit_price'] === '') {
                    $item['unit_price'] = 0;
                }
            }
            unset($item);
            
            $request->merge(['items' => empty($items) ? null : array_values($items)]);
        }

        $validated = $request->validate([
            'biller_id'                => $isDraft ? 'nullable|exists:customers,id' : 'required|exists:customers,id',
            'client_id'                => $isDraft ? 'nullable|exists:clients,id' : 'required|exists:clients,id',
            'customer_name'            => 'nullable|string|max:255',
            'client_po_id'             => 'nullable|exists:client_pos,id',
            'invoice_date'             => $isDraft ? 'nullable|date' : 'required|date',
            'due_date'                 => $isDraft ? 'nullable|date' : 'required|date|after_or_equal:invoice_date',
            'invoice_type'             => 'required|in:tax_invoice,without_gst,proforma',
            'notes'                    => 'nullable|string',
            'items'                    => $isDraft ? 'nullable|array' : 'required|array|min:1',
            'items.*.product_name'     => $isDraft ? 'nullable|string|max:255' : 'required|string|max:255',
            'items.*.description'      => 'nullable|string',
            'items.*.quantity'         => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01',
            'items.*.unit'             => 'nullable|string|max:50',
            'items.*.unit_price'       => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'include_payment_info'     => 'nullable|boolean',
            'bank_name'                => 'nullable|string|max:255',
            'bank_account_name'        => 'nullable|string|max:255',
            'bank_account_number'      => 'nullable|string|max:50',
            'bank_ifsc'                => 'nullable|string|max:20',
            'upi_id'                   => 'nullable|string|max:100',
            'billing_name'             => 'nullable|string|max:255',
            'billing_address'          => 'nullable|string',
            'billing_gstin'            => 'nullable|string|max:50',
            'shipping_name'            => 'nullable|string|max:255',
            'shipping_address'         => 'nullable|string',
            'shipping_gstin'           => 'nullable|string|max:50',
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
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (empty($item['product_name']) && $isDraft) continue;
                $product = Product::where('name', $item['product_name'])->first();
                $processedItems[] = [
                    'product_id' => $product ? $product->id : null,
                    'description' => $item['description'] ?? ($item['product_name'] ?? ''),
                    'quantity' => $item['quantity'] ?? 0,
                    'unit' => $item['unit'] ?? '',
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                ];
            }
        }

        $invoiceData = [
            'invoice_no' => $this->invoiceService->generateInvoiceNumber(),
            'biller_id' => $validated['biller_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'customer_id' => $customerId,
            'client_po_id' => $validated['client_po_id'] ?? null,
            'invoice_date' => $validated['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $validated['due_date'] ?? date('Y-m-d'),
            'invoice_type' => $validated['invoice_type'],
            'notes' => $validated['notes'] ?? null,
            'include_payment_info' => $validated['include_payment_info'] ?? false,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            'upi_id' => $validated['upi_id'] ?? null,
            'billing_name' => $validated['billing_name'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
            'billing_gstin' => $validated['billing_gstin'] ?? null,
            'shipping_name' => $validated['shipping_name'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'shipping_gstin' => $validated['shipping_gstin'] ?? null,
            'created_by' => auth()->id(),
            'status' => $isDraft ? 'draft' : 'published',
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
        $customers = Customer::all()->unique('name');
        $billers = Customer::all()->unique('name');
        $clients = \App\Models\Client::where('is_active', true)->get()->unique('name');
        $products = Product::all()->unique('name');
        $invoice->load('items');
        
        if ($invoice->status === 'draft') {
            $clients = \App\Models\Client::where('is_active', true)->get();
            return view('admin.invoices.edit_draft', compact('invoice', 'customers', 'billers', 'clients', 'products'));
        }
        
        return view('admin.invoices.edit', compact('invoice', 'customers', 'billers', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            $isDraft = $request->input('action') === 'draft';
            
            // Filter out empty rows (where product_name is empty) before validation
            if ($request->has('items')) {
                $items = array_filter($request->input('items'), function($item) {
                    return !empty($item['product_name']);
                });
                
                // Default missing quantity and unit price
                foreach ($items as &$item) {
                    if (!isset($item['quantity']) || $item['quantity'] === '') {
                        $item['quantity'] = 1;
                    }
                    if (!isset($item['unit_price']) || $item['unit_price'] === '') {
                        $item['unit_price'] = 0;
                    }
                }
                unset($item);
                
                $request->merge(['items' => empty($items) ? null : array_values($items)]);
            }

            $validated = $request->validate([
                'biller_id'                => $isDraft ? 'nullable|exists:customers,id' : 'required|exists:customers,id',
                'client_id'                => $isDraft ? 'nullable|exists:clients,id' : 'required|exists:clients,id',
                'customer_name'            => 'nullable|string|max:255',
                'client_po_id'             => 'nullable|exists:client_pos,id',
                'invoice_date'             => $isDraft ? 'nullable|date' : 'required|date',
                'due_date'                 => $isDraft ? 'nullable|date' : 'required|date|after_or_equal:invoice_date',
                'invoice_type'             => 'required|in:tax_invoice,without_gst,proforma',
                'notes'                    => 'nullable|string',
                'items'                    => $isDraft ? 'nullable|array' : 'required|array|min:1',
                'items.*.product_name'     => $isDraft ? 'nullable|string|max:255' : 'required|string|max:255',
                'items.*.description'      => 'nullable|string',
                'items.*.quantity'         => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01',
                'items.*.unit'             => 'nullable|string|max:50',
                'items.*.unit_price'       => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
                'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
                'include_payment_info'     => 'nullable|boolean',
                'bank_name'                => 'nullable|string|max:255',
                'bank_account_name'        => 'nullable|string|max:255',
                'bank_account_number'      => 'nullable|string|max:50',
                'bank_ifsc'                => 'nullable|string|max:20',
                'upi_id'                   => 'nullable|string|max:100',
                'billing_name'             => 'nullable|string|max:255',
                'billing_address'          => 'nullable|string',
                'billing_gstin'            => 'nullable|string|max:50',
                'shipping_name'            => 'nullable|string|max:255',
                'shipping_address'         => 'nullable|string',
                'shipping_gstin'           => 'nullable|string|max:50',
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
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    if (empty($item['product_name']) && $isDraft) continue;
                    $product = Product::where('name', $item['product_name'])->first();
                    $processedItems[] = [
                        'product_id' => $product ? $product->id : null,
                        'description' => $item['description'] ?? ($item['product_name'] ?? ''),
                        'quantity' => $item['quantity'] ?? 0,
                        'unit' => $item['unit'] ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                    ];
                }
            }

            $invoiceData = [
                'biller_id' => $validated['biller_id'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'customer_id' => $customerId,
                'client_po_id' => $validated['client_po_id'] ?? null,
                'invoice_date' => $validated['invoice_date'] ?? date('Y-m-d'),
                'due_date' => $validated['due_date'] ?? date('Y-m-d'),
                'invoice_type' => $validated['invoice_type'],
                'notes' => $validated['notes'] ?? null,
                'include_payment_info' => $validated['include_payment_info'] ?? false,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_ifsc' => $validated['bank_ifsc'] ?? null,
                'upi_id' => $validated['upi_id'] ?? null,
                'billing_name' => $validated['billing_name'] ?? null,
                'billing_address' => $validated['billing_address'] ?? null,
                'billing_gstin' => $validated['billing_gstin'] ?? null,
                'shipping_name' => $validated['shipping_name'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_gstin' => $validated['shipping_gstin'] ?? null,
                'status' => $isDraft ? 'draft' : 'published',
            ];

            $this->invoiceService->updateInvoice($invoice, $invoiceData, $processedItems);

            return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice updated successfully');
        }

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
        $invoice->load('customer', 'biller', 'client', 'items.product');
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
