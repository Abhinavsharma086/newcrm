cd domains/erp.hisabmittra.in/public_html
mkdir -p database/migrations
cat << 'EOF' > database/migrations/2026_09_14_052929_add_status_to_invoices_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('published')->after('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

EOF
mkdir -p app/Http/Controllers/Admin
cat << 'EOF' > app/Http/Controllers/Admin/InvoiceController.php
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
        $customers = Customer::all();
        $clients = \App\Models\Client::where('is_active', true)->get();
        $products = Product::all();
        $invoiceNo = $this->invoiceService->generateInvoiceNumber();
        
        return view('admin.invoices.create', compact('customers', 'clients', 'products', 'invoiceNo'));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('action') === 'draft';
        
        $validated = $request->validate([
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
        $customers = Customer::all();
        $products = Product::all();
        $invoice->load('items');
        
        if ($invoice->status === 'draft') {
            $clients = \App\Models\Client::where('is_active', true)->get();
            return view('admin.invoices.edit_draft', compact('invoice', 'customers', 'clients', 'products'));
        }
        
        return view('admin.invoices.edit', compact('invoice', 'customers', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            $isDraft = $request->input('action') === 'draft';
            $validated = $request->validate([
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

EOF
mkdir -p app/Models
cat << 'EOF' > app/Models/Invoice.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_no', 'status', 'customer_id', 'client_id', 'client_po_id', 'quotation_id', 'invoice_date', 'due_date',
        'subtotal', 'cgst', 'sgst', 'igst', 'total', 'payment_status',
        'paid_amount', 'notes', 'include_payment_info', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc', 'upi_id', 'upi_qr_image', 'created_by', 'branch_id'
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'cgst' => 'decimal:2',
            'sgst' => 'decimal:2',
            'igst' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    // Total GST (CGST + SGST + IGST)
    public function getTotalGstAttribute(): float
    {
        return round(($this->cgst ?? 0) + ($this->sgst ?? 0) + ($this->igst ?? 0), 2);
    }

    // Grand Total
    public function getTotalAmountAttribute(): float
    {
        return round(($this->subtotal ?? 0) + $this->total_gst, 2);
    }

    // Outstanding balance
    public function getBalanceDueAttribute(): float
    {
        return round(($this->total ?? 0) - ($this->paid_amount ?? 0), 2);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function clientPo()
    {
        return $this->belongsTo(ClientPo::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creditDebitNotes()
    {
        return $this->hasMany(CreditDebitNote::class);
    }
}

EOF
mkdir -p app/Services
cat << 'EOF' > app/Services/InvoiceService.php
<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected $gstService;

    public function __construct(GstCalculationService $gstService)
    {
        $this->gstService = $gstService;
    }

    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "INV-{$year}{$month}-";
        
        $lastInvoice = Invoice::where('invoice_no', 'like', $prefix . '%')
            ->orderBy('invoice_no', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createInvoice($data, $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $invoice = Invoice::create($data);
            
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $customerState = $invoice->customer ? $invoice->customer->state : ($invoice->client ? $invoice->client->address : 'Delhi');
                $companyState = config('app.company_state', 'Delhi');
                
                $lineTotal = $item['quantity'] * $item['unit_price'];
                
                if (isset($data['invoice_type']) && $data['invoice_type'] === 'without_gst') {
                    $gst = ['cgst' => 0, 'sgst' => 0, 'igst' => 0, 'total_tax' => 0];
                } else {
                    $gst = $this->gstService->calculateGst($lineTotal, $product->tax_rate, $customerState, $companyState);
                }
                
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $product->tax_rate,
                    'cgst' => $gst['cgst'],
                    'sgst' => $gst['sgst'],
                    'igst' => $gst['igst'],
                    'total' => $lineTotal + $gst['total_tax'],
                ]);
            }
            
            $this->recalculateInvoice($invoice);
            
            return $invoice->fresh();
        });
    }

    public function recalculateInvoice(Invoice $invoice)
    {
        $subtotal = 0;
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        
        foreach ($invoice->items as $item) {
            $lineSubtotal = $item->quantity * $item->unit_price;
            $subtotal += $lineSubtotal;
            $cgst += $item->cgst;
            $sgst += $item->sgst;
            $igst += $item->igst;
        }
        
        $total = $subtotal + $cgst + $sgst + $igst;
        
        $invoice->update([
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
        ]);
    }

    public function updateInvoice(Invoice $invoice, $data, $items)
    {
        return DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update($data);
            
            // Delete old items
            $invoice->items()->delete();
            
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $customerState = $invoice->customer ? $invoice->customer->state : ($invoice->client ? $invoice->client->address : 'Delhi');
                $companyState = config('app.company_state', 'Delhi');
                
                $lineTotal = $item['quantity'] * $item['unit_price'];
                
                if (isset($data['invoice_type']) && $data['invoice_type'] === 'without_gst') {
                    $gst = ['cgst' => 0, 'sgst' => 0, 'igst' => 0, 'total_tax' => 0];
                } else {
                    $gst = $this->gstService->calculateGst($lineTotal, $product ? $product->tax_rate : 0, $customerState, $companyState);
                }
                
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? ($product ? $product->name : ''),
                    'hsn_code' => $product ? $product->hsn_code : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $product ? $product->tax_rate : 0,
                    'cgst' => $gst['cgst'],
                    'sgst' => $gst['sgst'],
                    'igst' => $gst['igst'],
                    'total' => $lineTotal + $gst['total_tax'],
                ]);
            }
            
            $this->recalculateInvoice($invoice->fresh());
            
            return $invoice->fresh();
        });
    }
}

EOF
mkdir -p resources/views/admin/invoices
cat << 'EOF' > resources/views/admin/invoices/create.blade.php
@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<style>
    #itemsTable th { font-size: 0.85rem; font-weight: 600; white-space: nowrap; vertical-align: middle; background: #f0f4f8; }
    #itemsTable td { vertical-align: middle; padding: 0.4rem 0.35rem; }
    #itemsTable .form-control-sm, #itemsTable .form-select-sm { font-size: 0.85rem; padding: 0.35rem 0.5rem; }
    #itemsTable input[readonly] { background-color: #f8f9fa; font-weight: 600; color: #495057; border-color: #e9ecef; }
    .summary-bar { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem; }
    .summary-bar .summary-item { text-align: center; padding: 0.75rem 1rem; }
    .summary-bar .summary-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; }
    .summary-bar .summary-value { font-size: 1.25rem; font-weight: 700; color: #212529; }
    .summary-bar .summary-total { background: #0d6efd; border-radius: 0.5rem; color: #fff; }
    .summary-bar .summary-total .summary-label { color: rgba(255,255,255,0.8); }
    .summary-bar .summary-total .summary-value { color: #fff; }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i>Create Invoice</h2>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm" enctype="multipart/form-data">
        @csrf

        {{-- Invoice Details --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white"><strong><i class="fas fa-info-circle text-primary me-1"></i> Invoice Details</strong></div>
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice No</label>
                        <input type="text" class="form-control bg-light fw-bold" value="{{ $invoiceNo }}" readonly>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('invoice_type') is-invalid @enderror" name="invoice_type" id="invoice_type" required>
                            <option value="tax_invoice" {{ old('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                            <option value="without_gst" {{ old('invoice_type') == 'without_gst' ? 'selected' : '' }}>Without GST Bill</option>
                            <option value="proforma" {{ old('invoice_type') == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                        </select>
                        @error('invoice_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                               name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                               name="due_date" value="{{ old('due_date') }}" required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label class="form-label fw-semibold">Client (Billed To) <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror"
                                name="client_id" id="client_id" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Customer Account <span class="text-danger">*</span></label>
                        <select class="form-select select2-tags @error('customer_name') is-invalid @enderror" name="customer_name" required>
                            <option value="">Select or Type</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->name }}" {{ old('customer_name') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }} @if($c->company_name)({{ $c->company_name }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-boxes text-primary me-1"></i> Items / Services</strong>
                <button type="button" class="btn btn-sm btn-primary" id="addRow">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:250px">Product / Service</th>
                                <th style="min-width:180px">Description</th>
                                <th style="min-width:85px" class="text-center">Qty</th>
                                <th style="min-width:80px" class="text-center">UOM</th>
                                <th style="min-width:110px" class="text-end">Rate (₹)</th>
                                <th style="min-width:80px" class="text-center">Disc %</th>
                                <th style="min-width:80px" class="text-center">GST %</th>
                                <th style="min-width:115px" class="text-end">Taxable (₹)</th>
                                <th style="min-width:100px" class="text-end">GST (₹)</th>
                                <th style="min-width:120px" class="text-end">Total (₹)</th>
                                <th style="min-width:45px" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td>
                                    <select class="form-select form-select-sm select2-tags product-select" name="items[0][product_name]" required>
                                        <option value="">Select or Type Product</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->name }}"
                                                data-price="{{ $p->price }}"
                                                data-tax="{{ $p->tax_rate }}"
                                                data-unit="{{ $p->unit }}"
                                                data-hsn="{{ $p->hsn_code }}">
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" class="form-control form-control-sm item-desc" name="items[0][description]" placeholder="Description"></td>
                                <td><input type="number" class="form-control form-control-sm qty text-center" name="items[0][quantity]" value="1" min="0.01" step="0.01" required></td>
                                <td><input type="text" class="form-control form-control-sm uom text-center" name="items[0][unit]" placeholder="Nos"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm rate text-end" name="items[0][unit_price]" value="0" min="0" required></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm disc text-center" name="items[0][discount_percent]" value="0" min="0" max="100"></td>
                                <td><input type="number" class="form-control form-control-sm gst-rate text-center" value="0" readonly tabindex="-1"></td>
                                <td><input type="text" class="form-control form-control-sm taxable text-end" value="0.00" readonly tabindex="-1"></td>
                                <td><input type="text" class="form-control form-control-sm gst-amt text-end" value="0.00" readonly tabindex="-1"></td>
                                <td><input type="text" class="form-control form-control-sm total-amt text-end" value="0.00" readonly tabindex="-1"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash-alt"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Summary Bar + Actions --}}
        <div class="row g-4 mb-4">
            {{-- Left Column: Notes & Compliance --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sticky-note text-primary me-2"></i>Invoice Notes & Terms</h6>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Customer Notes / Payment Instructions</label>
                            <textarea class="form-control" name="notes" rows="3" style="resize: vertical; font-size: 0.9rem;" placeholder="Enter any payment instructions, bank notes, or delivery terms...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="include_payment_info" id="include_payment_info" value="1" {{ old('include_payment_info') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="include_payment_info">Include Bank Details & UPI QR on Bill</label>
                            </div>
                            <small class="text-muted d-block mt-1">Check this if you want to print your company's bank details and UPI scanner on the invoice PDF to receive payments easily.</small>
                        </div>
                        
                        {{-- Custom Bank Details Section (Hidden by default) --}}
                        <div id="bankDetailsSection" class="p-3 bg-white border rounded mb-3" style="display: none;">
                            <h6 class="fw-bold mb-3" style="font-size: 0.85rem;"><i class="fas fa-edit me-1"></i>Edit Payment Details for this Invoice</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', \App\Models\CompanySetting::get('bank_name', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Account Name</label>
                                    <input type="text" name="bank_account_name" class="form-control form-control-sm" value="{{ old('bank_account_name', \App\Models\CompanySetting::get('bank_account_name', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Account Number</label>
                                    <input type="text" name="bank_account_number" class="form-control form-control-sm" value="{{ old('bank_account_number', \App\Models\CompanySetting::get('bank_account_number', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">IFSC Code</label>
                                    <input type="text" name="bank_ifsc" class="form-control form-control-sm" value="{{ old('bank_ifsc', \App\Models\CompanySetting::get('bank_ifsc', '')) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control form-control-sm" value="{{ old('upi_id', \App\Models\CompanySetting::get('upi_id', '')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3">
                                    <i class="fas fa-shield-alt fa-lg"></i>
                                </div>
                                <div class="small">
                                    <strong class="text-dark">GST & Tax Compliance:</strong>
                                    <div class="text-muted">Calculations are automatically computed as per Indian GST rules with standard Round-Off.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Structured Billing Calculation & Actions --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calculator text-primary me-2"></i>Bill Amount Summary</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Taxable Subtotal:</span>
                            <span class="fw-bold fs-6 text-dark font-monospace" id="summaryTaxable">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Total GST (CGST + SGST / IGST):</span>
                            <span class="fw-bold fs-6 text-primary font-monospace" id="summaryGst">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Round Off:</span>
                            <span class="fw-semibold text-secondary font-monospace" id="summaryRoundOff">₹0.00</span>
                        </div>

                        {{-- Grand Total Highlight Box --}}
                        <div class="p-3 rounded-4 mb-3" style="background: linear-gradient(135deg, #1e40af, #2563eb); color: #ffffff; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Grand Total</span>
                                    <div class="small text-white opacity-75">Final Payable Amount</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-3 fw-bolder font-monospace text-white" id="summaryTotal">₹0.00</div>
                                </div>
                            </div>
                        </div>

                        {{-- Amount in Words Banner --}}
                        <div class="p-2 px-3 rounded-3 mb-4 d-flex align-items-center" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                            <i class="fas fa-receipt text-primary me-2"></i>
                            <span class="small text-muted fw-semibold me-1">In Words:</span>
                            <span class="small fw-bold text-dark text-truncate" id="amountInWords">Zero Rupees Only</span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="publish" class="btn btn-primary btn-lg flex-grow-1 rounded-pill shadow-sm" style="font-weight: 600; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-check-circle me-2"></i> Create GST Invoice
                            </button>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary btn-lg rounded-pill px-4" style="font-weight: 500;">
                                <i class="fas fa-save me-2"></i> Save as Draft
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4" style="font-weight: 500;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = 1;
const products = @json($products->keyBy('id'));

function calcRow(row) {
    const isWithoutGst = document.getElementById('invoice_type').value === 'without_gst';
    const qty   = parseFloat(row.querySelector('.qty').value) || 0;
    const rate  = parseFloat(row.querySelector('.rate').value) || 0;
    const disc  = parseFloat(row.querySelector('.disc').value) || 0;
    const gstR  = isWithoutGst ? 0 : (parseFloat(row.querySelector('.gst-rate').value) || 0);

    const lineAmt  = qty * rate;
    const discAmt  = lineAmt * disc / 100;
    const taxable  = lineAmt - discAmt;
    const gstAmt   = taxable * gstR / 100;
    const total    = taxable + gstAmt;

    row.querySelector('.taxable').value   = taxable.toFixed(2);
    row.querySelector('.gst-amt').value   = gstAmt.toFixed(2);
    row.querySelector('.total-amt').value = total.toFixed(2);
    updateSummary();
}

document.getElementById('invoice_type').addEventListener('change', function() {
    document.querySelectorAll('.item-row').forEach(row => calcRow(row));
    const isWithoutGst = this.value === 'without_gst';
    // Hide/Show GST columns based on type
    const gstCols = document.querySelectorAll('th:nth-child(7), th:nth-child(9), td:nth-child(7), td:nth-child(9)');
    gstCols.forEach(col => col.style.display = isWithoutGst ? 'none' : '');
    
    // Hide/Show Summary GST row
    const summaryGstRow = document.getElementById('summaryGst').closest('.d-flex');
    if (summaryGstRow) {
        summaryGstRow.style.display = isWithoutGst ? 'none' : 'flex';
    }
});

// Toggle Bank Details Section
const includePaymentCheckbox = document.getElementById('include_payment_info');
const bankDetailsSection = document.getElementById('bankDetailsSection');

function toggleBankDetails() {
    if (includePaymentCheckbox.checked) {
        bankDetailsSection.style.display = 'block';
    } else {
        bankDetailsSection.style.display = 'none';
    }
}

includePaymentCheckbox.addEventListener('change', toggleBankDetails);
toggleBankDetails(); // Run on load in case it's checked (e.g. old input)

function numberToWordsIndian(num) {
    if (num === 0) return 'Zero';
    const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                  'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                  'Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

    function twoDigits(n) {
        if (n < 20) return ones[n];
        return tens[Math.floor(n/10)] + (n%10 ? ' ' + ones[n%10] : '');
    }
    function threeDigits(n) {
        if (n >= 100) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' and ' + twoDigits(n%100) : '');
        return twoDigits(n);
    }

    let result = '';
    if (num >= 10000000) { result += twoDigits(Math.floor(num/10000000)) + ' Crore '; num %= 10000000; }
    if (num >= 100000)   { result += twoDigits(Math.floor(num/100000)) + ' Lakh ';   num %= 100000; }
    if (num >= 1000)     { result += twoDigits(Math.floor(num/1000)) + ' Thousand '; num %= 1000; }
    if (num > 0)         { result += threeDigits(Math.floor(num)); }
    return result.trim();
}

function amountToWords(amount) {
    const rupees = Math.floor(amount);
    const paise  = Math.round((amount - rupees) * 100);
    let words = numberToWordsIndian(rupees) + ' Rupees';
    if (paise > 0) words += ' and ' + numberToWordsIndian(paise) + ' Paise';
    return words + ' Only';
}

function updateSummary() {
    let taxable = 0, gst = 0, grand = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        taxable += parseFloat(row.querySelector('.taxable').value) || 0;
        gst     += parseFloat(row.querySelector('.gst-amt').value) || 0;
        grand   += parseFloat(row.querySelector('.total-amt').value) || 0;
    });

    const rounded  = Math.round(grand);
    const roundOff = rounded - grand;

    document.getElementById('summaryTaxable').textContent = '₹' + taxable.toFixed(2);
    document.getElementById('summaryGst').textContent     = '₹' + gst.toFixed(2);
    document.getElementById('summaryRoundOff').textContent = (roundOff >= 0 ? '+' : '') + '₹' + roundOff.toFixed(2);
    document.getElementById('summaryRoundOff').style.color = roundOff >= 0 ? '#198754' : '#dc3545';
    document.getElementById('summaryTotal').textContent   = '₹' + rounded.toFixed(2);
    document.getElementById('amountInWords').textContent  = rounded > 0 ? amountToWords(rounded) : 'Zero Rupees Only';
}

function makeRow(idx) {
    const first = document.querySelector('.item-row');
    const clone = first.cloneNode(true);
    $(clone).find('select').each(function () {
        let name = $(this).attr('name');
        if (name) {
            name = name.replace(/\[\d+\]/, '[' + idx + ']');
            $(this).attr('name', name);
        }
        
        if ($(this).hasClass('select2-tags') || $(this).hasClass('select2-hidden-accessible')) {
            $(this).removeClass('select2-hidden-accessible');
            $(this).removeAttr('data-select2-id tabindex aria-hidden');
            $(this).empty().append($('#itemsBody tr:first .product-select').html());
            $(this).val('').trigger('change.select2');
            
            $(this).select2({
                theme: 'bootstrap-5',
                tags: true,
                placeholder: "Select or Type Product",
                allowClear: true
            });
        }
    });
    clone.querySelectorAll('input').forEach(el => {
        if (el.classList.contains('qty')) el.value = 1;
        else if (el.classList.contains('rate') || el.classList.contains('disc')) el.value = 0;
        else if (el.classList.contains('taxable') || el.classList.contains('gst-amt') || el.classList.contains('total-amt')) el.value = '0.00';
        else if (!el.classList.contains('gst-rate')) el.value = '';
    });
    clone.querySelector('.gst-rate').value = 0;
    return clone;
}

document.getElementById('addRow').addEventListener('click', function () {
    const tbody = document.getElementById('itemsBody');
    tbody.appendChild(makeRow(rowIndex++));
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
            updateSummary();
        }
    }
});

$(document).on('change', '.product-select', function(e) {
    const row = $(this).closest('.item-row')[0];
    
    if (this.selectedIndex > -1) {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.price !== undefined) {
            row.querySelector('.rate').value     = selectedOption.dataset.price || 0;
            row.querySelector('.uom').value      = selectedOption.dataset.unit || '';
            row.querySelector('.gst-rate').value = selectedOption.dataset.tax || 0;
            calcRow(row);
        }
    }
});

document.addEventListener('input', function (e) {
    if (e.target.closest('.item-row') &&
        (e.target.classList.contains('qty') ||
         e.target.classList.contains('rate') ||
         e.target.classList.contains('disc'))) {
        calcRow(e.target.closest('.item-row'));
    }
});
</script>
@endpush

EOF
mkdir -p resources/views/admin/invoices
cat << 'EOF' > resources/views/admin/invoices/index.blade.php
@extends('layouts.app')

@section('title', 'Invoices')

@section('breadcrumb')
<li class="breadcrumb-item active">Invoices</li>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i>Invoice Management</h2>
        <div>
            <button type="button" class="btn btn-outline-success me-2 d-none" id="bulkPayBtn" data-bs-toggle="modal" data-bs-target="#bulkPaymentModal">
                <i class="fas fa-money-bill-wave me-1"></i> Bulk Pay Selected (<span id="selectedCount">0</span>)
            </button>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Invoice
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Invoices</div>
                            <div class="fs-3 fw-bold text-dark mt-1">{{ $invoices->count() }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Billed</div>
                            <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($invoices->sum('total'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f1f5f9; color: #475569;">
                            <i class="fas fa-receipt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Received</div>
                            <div class="fs-4 fw-bold text-success mt-1">₹{{ number_format($invoices->sum('paid_amount'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Balance Outstanding</div>
                            <div class="fs-4 fw-bold text-danger mt-1">₹{{ number_format($invoices->sum('total') - $invoices->sum('paid_amount'), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #fff1f2; color: #e11d48;">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Invoice Type</label>
                    <select name="invoice_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="without_gst" {{ request('invoice_type') == 'without_gst' ? 'selected' : '' }}>Without GST Bill</option>
                        <option value="proforma" {{ request('invoice_type') == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-light btn-sm border"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="invoicesTable" class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr class="text-secondary small fw-bold">
                            <th width="30px" class="no-export ps-4"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                            <th class="py-3">Invoice #</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Due Date</th>
                            <th class="text-end py-3">Total</th>
                            <th class="text-end py-3">Paid</th>
                            <th class="text-end py-3">Balance Due</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td class="no-export ps-4">
                                @if($invoice->balance_due > 0)
                                    <input type="checkbox" class="form-check-input invoice-checkbox" value="{{ $invoice->id }}" data-balance="{{ $invoice->balance_due }}">
                                @else
                                    <input type="checkbox" class="form-check-input" disabled>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">{{ $invoice->invoice_no }}</a>
                                <br>
                                @if($invoice->status === 'draft')
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">Draft</span>
                                @else
                                    @if($invoice->invoice_type === 'without_gst')
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">Without GST</span>
                                    @elseif($invoice->invoice_type === 'proforma')
                                        <span class="badge bg-info" style="font-size: 0.65rem;">Proforma</span>
                                    @else
                                        <span class="badge bg-primary" style="font-size: 0.65rem;">Tax Invoice</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $invoice->customer->name ?? 'N/A' }}</div>
                                @if($invoice->customer && $invoice->customer->company_name)
                                    <div class="text-muted small">{{ $invoice->customer->company_name }}</div>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td>
                                <span class="text-muted small">{{ $invoice->due_date->format('d M Y') }}</span>
                                @if($invoice->payment_status !== 'paid' && $invoice->due_date < now())
                                <span class="badge rounded-pill px-2 py-0 ms-1" style="background:#fee2e2; color:#991b1b; font-size:0.65rem; border:1px solid #fca5a5;">Overdue</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($invoice->balance_due, 2) }}
                            </td>
                            <td class="text-center">
                                @if($invoice->status === 'draft')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600; font-size:0.75rem;">Draft</span>
                                @elseif($invoice->payment_status == 'paid')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:600; font-size:0.75rem;">Paid</span>
                                @elseif($invoice->payment_status == 'partial')
                                    <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fcd34d; font-weight:600; font-size:0.75rem;">Partial</span>
                                @else
                                    <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:600; font-size:0.75rem;">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @if($invoice->status === 'draft')
                                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-light text-warning border" title="Edit Draft">
                                            <i class="fas fa-edit"></i> Edit Draft
                                        </a>
                                    @else
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-light text-primary border" title="View GST Invoice">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm btn-light text-danger border" title="Download GST Bill PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light text-success border open-wa-modal" 
                                                data-phone="{{ $invoice->customer->phone ?? '' }}"
                                                data-name="{{ $invoice->customer->name ?? 'Customer' }}"
                                                data-no="{{ $invoice->invoice_no }}"
                                                data-date="{{ $invoice->invoice_date->format('d M Y') }}"
                                                data-total="₹{{ number_format($invoice->total, 2) }}"
                                                data-status="{{ strtoupper($invoice->payment_status) }}"
                                                data-balance="₹{{ number_format($invoice->balance_due, 2) }}"
                                                data-pdf="{{ route('admin.invoices.pdf', $invoice) }}"
                                                title="Send GST Bill on WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Bulk Payment Modal -->
<div class="modal fade" id="bulkPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Record Bulk Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.invoices.bulk-mark-paid') }}" method="POST" id="bulkPaymentForm">
                @csrf
                <div class="modal-body">
                    <div id="bulkSelectedIdsContainer"></div>
                    <div class="alert alert-info">
                        <strong>Total Selected Balance: ₹<span id="bulkTotalBalanceDisplay">0.00</span></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="neft">NEFT</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No / Transaction ID</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Mark Selected as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WhatsApp Share Modal for Invoices Index -->
<div class="modal fade" id="indexWhatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send GST Bill on WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-2 bg-light rounded border text-muted small d-flex align-items-center">
                    <i class="fas fa-headset text-success me-2 fs-5"></i>
                    <div>
                        <strong>Sender / Helpline Number:</strong> <span class="badge bg-success">+91 9099916179</span>
                        <div class="small">Metric Qube Energy Pvt. Ltd.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer WhatsApp Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                        <input type="tel" class="form-control" id="modalWaPhone" placeholder="Enter 10-digit mobile number">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Preview</label>
                    <textarea class="form-control font-monospace" id="modalWaMessage" rows="10" style="font-size: 0.85rem;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalDownloadPdfBtn" class="btn btn-outline-danger me-auto" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalSendWaBtn">
                    <i class="fab fa-whatsapp me-1"></i> Send via WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#invoicesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'excel',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'pdf',
                exportOptions: { columns: ':not(.no-export)' }
            },
            {
                extend: 'print',
                exportOptions: { columns: ':not(.no-export)' }
            }
        ],
        order: [[1, 'desc']],
        columnDefs: [
            { targets: 0, orderable: false }
        ]
    });

    // Handle Bulk Selection
    function updateBulkButton() {
        const checkedBoxes = $('.invoice-checkbox:checked');
        const count = checkedBoxes.length;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#bulkPayBtn').removeClass('d-none');
        } else {
            $('#bulkPayBtn').addClass('d-none');
        }

        // Fill modal fields on click
        let totalVal = 0;
        let htmlInputs = '';
        checkedBoxes.each(function() {
            const id = $(this).val();
            const bal = parseFloat($(this).data('balance')) || 0;
            totalVal += bal;
            htmlInputs += `<input type="hidden" name="invoice_ids[]" value="${id}">`;
        });
        $('#bulkTotalBalanceDisplay').text(totalVal.toFixed(2));
        $('#bulkSelectedIdsContainer').html(htmlInputs);
    }

    $('#selectAllCheckbox').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.invoice-checkbox:not(:disabled)').prop('checked', isChecked);
        updateBulkButton();
    });

    $(document).on('change', '.invoice-checkbox', function() {
        updateBulkButton();
    });

    // Handle WhatsApp Share Modal
    $(document).on('click', '.open-wa-modal', function() {
        const phone   = $(this).data('phone') || '';
        const name    = $(this).data('name') || 'Customer';
        const no      = $(this).data('no') || '';
        const date    = $(this).data('date') || '';
        const total   = $(this).data('total') || '₹0.00';
        const status  = $(this).data('status') || 'UNPAID';
        const balance = $(this).data('balance') || '₹0.00';
        const pdf     = $(this).data('pdf') || '';
        const publicBillUrl = window.location.origin + '/bill/' + no;

        $('#modalWaPhone').val(phone);
        $('#modalDownloadPdfBtn').attr('href', pdf);

        const msg = `*TAX INVOICE / GST BILL*\n*Metric Qube Energy Pvt. Ltd.*\n----------------------------------\nDear *${name}*,\n\nThank you for your business! Here are your GST Invoice details:\n\n📄 *Invoice No:* ${no}\n📅 *Invoice Date:* ${date}\n💰 *Total Amount:* ${total}\n💳 *Payment Status:* ${status}\n💵 *Balance Due:* ${balance}\n\n📥 *View / Download GST Bill (PDF):*\n${publicBillUrl}\n\nFor any queries or assistance, please contact us at *+91 9099916179*.\n\nBest Regards,\n*Metric Qube Energy Pvt. Ltd.*\n📞 Helpline: +91 9099916179`;

        $('#modalWaMessage').val(msg);
        const waModal = new bootstrap.Modal(document.getElementById('indexWhatsappModal'));
        waModal.show();
    });

    $('#modalSendWaBtn').on('click', function() {
        let phone = $('#modalWaPhone').val().replace(/[^0-9]/g, '');
        const message = encodeURIComponent($('#modalWaMessage').val());
        
        if (phone.length === 10) {
            phone = '91' + phone;
        }
        
        if (!phone || phone.length < 10) {
            alert('Please enter a valid 10-digit mobile number.');
            return;
        }
        
        window.open('https://api.whatsapp.com/send?phone=' + phone + '&text=' + message, '_blank');
    });
});
</script>
@endpush

EOF
mkdir -p resources/views/admin/invoices
cat << 'EOF' > resources/views/admin/invoices/show.blade.php
@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_no)

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i>Invoice #{{ $invoice->invoice_no }}</h2>
        <div class="btn-group">
            @if($invoice->status === 'draft')
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit Draft
                </a>
            @else
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Download GST Bill (PDF)
                </a>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                    <i class="fab fa-whatsapp me-1"></i>Send on WhatsApp
                </button>
                @if($invoice->balance_due > 0)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-rupee-sign me-1"></i>Record Payment
                </button>
                @endif
            @endif
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Invoice Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-6">
                            <img src="{{ asset('MQ logo.png') }}" alt="Logo" style="max-height:55px; width:auto; object-fit:contain;" class="mb-2">
                            <h5 class="mb-1 fw-bold text-dark">{{ config('app.company_name', 'Metric Qube Energy Pvt. Ltd.') }}</h5>
                            <small class="text-muted">{{ config('app.company_address', 'Jaipur, Rajasthan, India') }}</small>
                        </div>
                        <div class="col-6 text-end">
                            <h3 class="fw-bold mb-1" style="color: #1e40af; letter-spacing: 0.5px;">TAX INVOICE</h3>
                            <p class="mb-1 text-muted">Invoice No: <strong class="text-dark">#{{ $invoice->invoice_no }}</strong></p>
                            <p class="mb-1 text-muted">Date: <strong class="text-dark">{{ $invoice->invoice_date->format('d M Y') }}</strong></p>
                            <p class="mb-1 text-muted">Due Date: <strong class="text-dark">{{ $invoice->due_date->format('d M Y') }}</strong></p>
                            @if($invoice->status === 'draft')
                                <span class="badge rounded-pill px-3 py-1 fs-6" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600;">DRAFT</span>
                            @elseif($invoice->payment_status == 'paid')
                                <span class="badge rounded-pill px-3 py-1 fs-6" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:600;">PAID</span>
                            @elseif($invoice->payment_status == 'partial')
                                <span class="badge rounded-pill px-3 py-1 fs-6" style="background:#fef3c7; color:#b45309; border:1px solid #fcd34d; font-weight:600;">PARTIAL</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1 fs-6" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:600;">UNPAID</span>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="text-primary fw-bold mb-2"><i class="fas fa-user-circle me-1"></i> BILL TO (CUSTOMER DETAILS)</h6>
                                <div class="fs-5 fw-bold text-dark">{{ $invoice->customer->name ?? 'N/A' }}</div>
                                @if($invoice->customer)
                                    @if($invoice->customer->company_name)
                                        <div class="text-muted"><i class="fas fa-building fa-sm me-1"></i> {{ $invoice->customer->company_name }}</div>
                                    @endif
                                    @if($invoice->customer->address)
                                        <div><i class="fas fa-map-marker-alt fa-sm me-1 text-danger"></i> {{ $invoice->customer->address }}</div>
                                    @endif
                                    @if($invoice->customer->city || $invoice->customer->state || $invoice->customer->pin)
                                        <div class="text-muted small ps-3">{{ implode(', ', array_filter([$invoice->customer->city, $invoice->customer->state, $invoice->customer->pin])) }}</div>
                                    @endif
                                    @if($invoice->customer->gstin)
                                        <div class="mt-2">
                                            <span class="badge bg-primary fs-6 px-2 py-1"><i class="fas fa-id-card me-1"></i> GSTIN: {{ $invoice->customer->gstin }}</span>
                                        </div>
                                    @endif
                                    @if($invoice->customer->phone)
                                        <div class="mt-1"><i class="fas fa-phone fa-sm me-1 text-success"></i> {{ $invoice->customer->phone }}</div>
                                    @endif
                                    @if($invoice->customer->email)
                                        <div><i class="fas fa-envelope fa-sm me-1 text-info"></i> {{ $invoice->customer->email }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if($invoice->client)
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="text-secondary fw-bold mb-2"><i class="fas fa-briefcase me-1"></i> CLIENT / BILLED TO</h6>
                                <strong class="fs-6">{{ $invoice->client->name }}</strong>
                                @if($invoice->client->address) <div class="text-muted">{{ $invoice->client->address }}</div>@endif
                                @if($invoice->client->phone) <div><i class="fas fa-phone fa-sm me-1"></i> {{ $invoice->client->phone }}</div>@endif
                                @if($invoice->client->email) <div><i class="fas fa-envelope fa-sm me-1"></i> {{ $invoice->client->email }}</div>@endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>HSN/SAC</th>
                                    <th class="text-end">Qty</th>
                                    <th>UOM</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Disc%</th>
                                    <th class="text-end">Taxable</th>
                                    <th class="text-end">CGST</th>
                                    <th class="text-end">SGST</th>
                                    <th class="text-end">IGST</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        {{ $item->description ?: ($item->product->name ?? 'N/A') }}
                                        @if($item->product) <br><small class="text-muted">{{ $item->product->name }}</small>@endif
                                    </td>
                                    <td>{{ $item->hsn_code ?? $item->product->hsn_code ?? '-' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td>{{ $item->unit ?? $item->product->unit ?? '-' }}</td>
                                    <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ $item->discount_percent ?? 0 }}%</td>
                                    <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->cgst, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->sgst, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->igst, 2) }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">Subtotal:</th>
                                    <th class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</th>
                                    <th class="text-end">₹{{ number_format($invoice->cgst, 2) }}</th>
                                    <th class="text-end">₹{{ number_format($invoice->sgst, 2) }}</th>
                                    <th class="text-end">₹{{ number_format($invoice->igst, 2) }}</th>
                                    <th class="text-end">₹{{ number_format($invoice->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($invoice->notes)
                    <div class="alert alert-info">
                        <strong>Notes:</strong> {{ $invoice->notes }}
                    </div>
                    @endif

                    @if($invoice->include_payment_info)
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white py-2">
                            <strong><i class="fas fa-university me-1"></i> Payment & Bank Details</strong>
                        </div>
                        <div class="card-body bg-light row align-items-center">
                            <div class="col-md-8">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Bank Name:</strong> {{ $invoice->bank_name ?: 'N/A' }}</li>
                                    <li><strong>Account Name:</strong> {{ $invoice->bank_account_name ?: 'N/A' }}</li>
                                    <li><strong>Account Number:</strong> {{ $invoice->bank_account_number ?: 'N/A' }}</li>
                                    <li><strong>IFSC Code:</strong> {{ $invoice->bank_ifsc ?: 'N/A' }}</li>
                                    <li><strong>UPI ID:</strong> {{ $invoice->upi_id ?: 'N/A' }}</li>
                                </ul>
                            </div>
                            <div class="col-md-4 text-center">
                                @php
                                    $upiId = $invoice->upi_id ?: \App\Models\CompanySetting::get('upi_id');
                                    $payeeName = $invoice->bank_account_name ?: \App\Models\CompanySetting::get('bank_account_name', config('app.name'));
                                    $amount = $invoice->total;
                                    
                                    if ($upiId) {
                                        $qrData = "upi://pay?pa={$upiId}&pn={$payeeName}&am={$amount}&cu=INR";
                                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                                    } else {
                                        // Fallback to a placeholder QR if UPI ID is not set
                                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode("UPI ID Not Set");
                                    }
                                @endphp
                                
                                <img src="{{ $qrUrl }}" alt="UPI QR" class="img-thumbnail" style="max-height: 120px;">
                                <div class="small text-muted mt-1 fw-bold">Scan to Pay</div>
                                @if(!$upiId)
                                    <div class="small text-danger mt-1" style="font-size: 10px;">(Please add UPI ID)</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            @if($invoice->payments->count() > 0)
            <div class="card">
                <div class="card-header"><strong><i class="fas fa-history me-1"></i>Payment History</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $pay)
                            <tr>
                                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($pay->amount, 2) }}</td>
                                <td>{{ ucfirst($pay->payment_method ?? $pay->method ?? '-') }}</td>
                                <td>{{ $pay->reference_no ?? $pay->reference ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark text-white"><strong>Invoice Summary</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td>Subtotal</td><td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td></tr>
                        <tr><td>CGST</td><td class="text-end">₹{{ number_format($invoice->cgst, 2) }}</td></tr>
                        <tr><td>SGST</td><td class="text-end">₹{{ number_format($invoice->sgst, 2) }}</td></tr>
                        <tr><td>IGST</td><td class="text-end">₹{{ number_format($invoice->igst, 2) }}</td></tr>
                        <tr class="table-dark"><td><strong>Grand Total</strong></td><td class="text-end fw-bold">₹{{ number_format($invoice->total, 2) }}</td></tr>
                        <tr class="table-success"><td>Paid Amount</td><td class="text-end text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                        <tr class="{{ $invoice->balance_due > 0 ? 'table-danger' : 'table-success' }}">
                            <td><strong>Balance Due</strong></td>
                            <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($invoice->balance_due, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($invoice->status !== 'draft')
                @if($invoice->balance_due > 0)
                <div class="d-grid">
                    <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-rupee-sign me-2"></i>Record Payment
                    </button>
                </div>
                @else
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                    <strong>Fully Paid!</strong>
                </div>
                @endif
            @else
                <div class="alert alert-warning text-center">
                    <i class="fas fa-pencil-alt fa-2x mb-2"></i><br>
                    <strong>Draft Invoice</strong><br>
                    <small>Publish to record payments.</small>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if($invoice->balance_due > 0)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-rupee-sign me-2"></i>Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.invoices.record-payment', $invoice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Balance Due: ₹{{ number_format($invoice->balance_due, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="amount"
                               value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="neft">NEFT</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No / Transaction ID</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- WhatsApp Share Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send GST Bill on WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-2 bg-light rounded border text-muted small d-flex align-items-center">
                    <i class="fas fa-headset text-success me-2 fs-5"></i>
                    <div>
                        <strong>Sender / Helpline Number:</strong> <span class="badge bg-success">+91 9099916179</span>
                        <div class="small">Metric Qube Energy Pvt. Ltd.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer WhatsApp Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                        <input type="tel" class="form-control" id="waPhone" value="{{ $invoice->customer->phone ?? '' }}" placeholder="Enter 10-digit mobile number">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Preview</label>
                    <textarea class="form-control font-monospace" id="waMessage" rows="10" style="font-size: 0.85rem;">*TAX INVOICE / GST BILL*
*Metric Qube Energy Pvt. Ltd.*
----------------------------------
Dear *{{ $invoice->customer->name ?? 'Customer' }}*,

Thank you for your business! Here are your GST Invoice details:

📄 *Invoice No:* {{ $invoice->invoice_no }}
📅 *Invoice Date:* {{ $invoice->invoice_date->format('d M Y') }}
💰 *Total Amount:* ₹{{ number_format($invoice->total, 2) }}
💳 *Payment Status:* {{ strtoupper($invoice->payment_status) }}
💵 *Balance Due:* ₹{{ number_format($invoice->balance_due, 2) }}

📥 *View / Download GST Bill (PDF):*
{{ url('/bill/' . $invoice->invoice_no) }}

For any queries or assistance, please contact us at *+91 9099916179*.

Best Regards,
*Metric Qube Energy Pvt. Ltd.*
📞 Helpline: +91 9099916179</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline-danger me-auto" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="sendWaBtn">
                    <i class="fab fa-whatsapp me-1"></i> Send via WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendBtn = document.getElementById('sendWaBtn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            let phone = document.getElementById('waPhone').value.replace(/[^0-9]/g, '');
            const message = encodeURIComponent(document.getElementById('waMessage').value);
            
            if (phone.length === 10) {
                phone = '91' + phone;
            }
            
            if (!phone || phone.length < 10) {
                alert('Please enter a valid 10-digit mobile number.');
                return;
            }
            
            window.open('https://api.whatsapp.com/send?phone=' + phone + '&text=' + message, '_blank');
        });
    }
});
</script>
@endpush

EOF
mkdir -p resources/views/admin/invoices
cat << 'EOF' > resources/views/admin/invoices/edit_draft.blade.php
@extends('layouts.admin')

@section('title', 'Edit Draft Invoice')

@section('content')
<style>
    #itemsTable th { font-size: 0.85rem; font-weight: 600; white-space: nowrap; vertical-align: middle; background: #f0f4f8; }
    #itemsTable td { vertical-align: middle; padding: 0.4rem 0.35rem; }
    #itemsTable .form-control-sm, #itemsTable .form-select-sm { font-size: 0.85rem; padding: 0.35rem 0.5rem; }
    #itemsTable input[readonly] { background-color: #f8f9fa; font-weight: 600; color: #495057; border-color: #e9ecef; }
    .summary-bar { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem; }
    .summary-bar .summary-item { text-align: center; padding: 0.75rem 1rem; }
    .summary-bar .summary-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; }
    .summary-bar .summary-value { font-size: 1.25rem; font-weight: 700; color: #212529; }
    .summary-bar .summary-total { background: #0d6efd; border-radius: 0.5rem; color: #fff; }
    .summary-bar .summary-total .summary-label { color: rgba(255,255,255,0.8); }
    .summary-bar .summary-total .summary-value { color: #fff; }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i>Edit Draft Invoice</h2>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST" id="invoiceForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Invoice Details --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white"><strong><i class="fas fa-info-circle text-primary me-1"></i> Invoice Details</strong></div>
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice No</label>
                        <input type="text" class="form-control bg-light fw-bold" value="{{ $invoice->invoice_no }}" readonly>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('invoice_type') is-invalid @enderror" name="invoice_type" id="invoice_type" required>
                            <option value="tax_invoice" {{ old('invoice_type', $invoice->invoice_type) == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                            <option value="without_gst" {{ old('invoice_type', $invoice->invoice_type) == 'without_gst' ? 'selected' : '' }}>Without GST Bill</option>
                            <option value="proforma" {{ old('invoice_type', $invoice->invoice_type) == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                        </select>
                        @error('invoice_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                               name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                               name="due_date" value="{{ old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}" required>
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label class="form-label fw-semibold">Client (Billed To) <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror"
                                name="client_id" id="client_id" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Customer Account <span class="text-danger">*</span></label>
                        <select class="form-select select2-tags @error('customer_name') is-invalid @enderror" name="customer_name" required>
                            <option value="">Select or Type</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->name }}" {{ old('customer_name', optional($invoice->customer)->name) == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }} @if($c->company_name)({{ $c->company_name }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-boxes text-primary me-1"></i> Items / Services</strong>
                <button type="button" class="btn btn-sm btn-primary" id="addRow">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:250px">Product / Service</th>
                                <th style="min-width:180px">Description</th>
                                <th style="min-width:85px" class="text-center">Qty</th>
                                <th style="min-width:80px" class="text-center">UOM</th>
                                <th style="min-width:110px" class="text-end">Rate (₹)</th>
                                <th style="min-width:80px" class="text-center">Disc %</th>
                                <th style="min-width:80px" class="text-center">GST %</th>
                                <th style="min-width:115px" class="text-end">Taxable (₹)</th>
                                <th style="min-width:100px" class="text-end">GST (₹)</th>
                                <th style="min-width:120px" class="text-end">Total (₹)</th>
                                <th style="min-width:45px" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php $rowIndex = 0; @endphp
                            @forelse($invoice->items as $idx => $invItem)
                                <tr class="item-row">
                                    <td>
                                        <select class="form-select form-select-sm select2-tags product-select" name="items[{{ $idx }}][product_name]" required>
                                            <option value="">Select or Type Product</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->name }}"
                                                    data-price="{{ $p->price }}"
                                                    data-tax="{{ $p->tax_rate }}"
                                                    data-unit="{{ $p->unit }}"
                                                    data-hsn="{{ $p->hsn_code }}"
                                                    {{ (optional($invItem->product)->name == $p->name || $invItem->description == $p->name) ? 'selected' : '' }}>
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-desc" name="items[{{ $idx }}][description]" value="{{ $invItem->description }}" placeholder="Description"></td>
                                    <td><input type="number" class="form-control form-control-sm qty text-center" name="items[{{ $idx }}][quantity]" value="{{ $invItem->quantity }}" min="0.01" step="0.01" required></td>
                                    <td><input type="text" class="form-control form-control-sm uom text-center" name="items[{{ $idx }}][unit]" value="{{ $invItem->unit }}" placeholder="Nos"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm rate text-end" name="items[{{ $idx }}][unit_price]" value="{{ $invItem->unit_price }}" min="0" required></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm disc text-center" name="items[{{ $idx }}][discount_percent]" value="{{ $invItem->discount_percent ?? 0 }}" min="0" max="100"></td>
                                    <td><input type="number" class="form-control form-control-sm gst-rate text-center" value="{{ $invItem->tax_rate }}" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm taxable text-end" value="{{ number_format(($invItem->quantity * $invItem->unit_price) - (($invItem->quantity * $invItem->unit_price) * ($invItem->discount_percent ?? 0) / 100), 2, '.', '') }}" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm gst-amt text-end" value="{{ number_format($invItem->cgst + $invItem->sgst + $invItem->igst, 2, '.', '') }}" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm total-amt text-end" value="{{ $invItem->total }}" readonly tabindex="-1"></td>
                                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash-alt"></i></button></td>
                                </tr>
                                @php $rowIndex = $idx + 1; @endphp
                            @empty
                                <tr class="item-row">
                                    <td>
                                        <select class="form-select form-select-sm select2-tags product-select" name="items[0][product_name]" required>
                                            <option value="">Select or Type Product</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->name }}"
                                                    data-price="{{ $p->price }}"
                                                    data-tax="{{ $p->tax_rate }}"
                                                    data-unit="{{ $p->unit }}"
                                                    data-hsn="{{ $p->hsn_code }}">
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-desc" name="items[0][description]" placeholder="Description"></td>
                                    <td><input type="number" class="form-control form-control-sm qty text-center" name="items[0][quantity]" value="1" min="0.01" step="0.01" required></td>
                                    <td><input type="text" class="form-control form-control-sm uom text-center" name="items[0][unit]" placeholder="Nos"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm rate text-end" name="items[0][unit_price]" value="0" min="0" required></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm disc text-center" name="items[0][discount_percent]" value="0" min="0" max="100"></td>
                                    <td><input type="number" class="form-control form-control-sm gst-rate text-center" value="0" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm taxable text-end" value="0.00" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm gst-amt text-end" value="0.00" readonly tabindex="-1"></td>
                                    <td><input type="text" class="form-control form-control-sm total-amt text-end" value="0.00" readonly tabindex="-1"></td>
                                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash-alt"></i></button></td>
                                </tr>
                                @php $rowIndex = 1; @endphp
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Summary Bar + Actions --}}
        <div class="row g-4 mb-4">
            {{-- Left Column: Notes & Compliance --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sticky-note text-primary me-2"></i>Invoice Notes & Terms</h6>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Customer Notes / Payment Instructions</label>
                            <textarea class="form-control" name="notes" rows="3" style="resize: vertical; font-size: 0.9rem;" placeholder="Enter any payment instructions, bank notes, or delivery terms...">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="include_payment_info" id="include_payment_info" value="1" {{ old('include_payment_info', $invoice->include_payment_info) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="include_payment_info">Include Bank Details & UPI QR on Bill</label>
                            </div>
                            <small class="text-muted d-block mt-1">Check this if you want to print your company's bank details and UPI scanner on the invoice PDF to receive payments easily.</small>
                        </div>
                        
                        {{-- Custom Bank Details Section (Hidden by default) --}}
                        <div id="bankDetailsSection" class="p-3 bg-white border rounded mb-3" style="display: none;">
                            <h6 class="fw-bold mb-3" style="font-size: 0.85rem;"><i class="fas fa-edit me-1"></i>Edit Payment Details for this Invoice</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $invoice->bank_name ?? \App\Models\CompanySetting::get('bank_name', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Account Name</label>
                                    <input type="text" name="bank_account_name" class="form-control form-control-sm" value="{{ old('bank_account_name', $invoice->bank_account_name ?? \App\Models\CompanySetting::get('bank_account_name', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Account Number</label>
                                    <input type="text" name="bank_account_number" class="form-control form-control-sm" value="{{ old('bank_account_number', $invoice->bank_account_number ?? \App\Models\CompanySetting::get('bank_account_number', '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">IFSC Code</label>
                                    <input type="text" name="bank_ifsc" class="form-control form-control-sm" value="{{ old('bank_ifsc', $invoice->bank_ifsc ?? \App\Models\CompanySetting::get('bank_ifsc', '')) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control form-control-sm" value="{{ old('upi_id', $invoice->upi_id ?? \App\Models\CompanySetting::get('upi_id', '')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3">
                                    <i class="fas fa-shield-alt fa-lg"></i>
                                </div>
                                <div class="small">
                                    <strong class="text-dark">GST & Tax Compliance:</strong>
                                    <div class="text-muted">Calculations are automatically computed as per Indian GST rules with standard Round-Off.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Structured Billing Calculation & Actions --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calculator text-primary me-2"></i>Bill Amount Summary</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Taxable Subtotal:</span>
                            <span class="fw-bold fs-6 text-dark font-monospace" id="summaryTaxable">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Total GST (CGST + SGST / IGST):</span>
                            <span class="fw-bold fs-6 text-primary font-monospace" id="summaryGst">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Round Off:</span>
                            <span class="fw-semibold text-secondary font-monospace" id="summaryRoundOff">₹0.00</span>
                        </div>

                        {{-- Grand Total Highlight Box --}}
                        <div class="p-3 rounded-4 mb-3" style="background: linear-gradient(135deg, #1e40af, #2563eb); color: #ffffff; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Grand Total</span>
                                    <div class="small text-white opacity-75">Final Payable Amount</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-3 fw-bolder font-monospace text-white" id="summaryTotal">₹0.00</div>
                                </div>
                            </div>
                        </div>

                        {{-- Amount in Words Banner --}}
                        <div class="p-2 px-3 rounded-3 mb-4 d-flex align-items-center" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                            <i class="fas fa-receipt text-primary me-2"></i>
                            <span class="small text-muted fw-semibold me-1">In Words:</span>
                            <span class="small fw-bold text-dark text-truncate" id="amountInWords">Zero Rupees Only</span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="publish" class="btn btn-primary btn-lg flex-grow-1 rounded-pill shadow-sm" style="font-weight: 600; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-check-circle me-2"></i> Publish GST Invoice
                            </button>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary btn-lg rounded-pill px-4" style="font-weight: 500;">
                                <i class="fas fa-save me-2"></i> Save as Draft
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4" style="font-weight: 500;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = {{ $rowIndex ?? 1 }};
const products = @json($products->keyBy('id'));

function calcRow(row) {
    const isWithoutGst = document.getElementById('invoice_type').value === 'without_gst';
    const qty   = parseFloat(row.querySelector('.qty').value) || 0;
    const rate  = parseFloat(row.querySelector('.rate').value) || 0;
    const disc  = parseFloat(row.querySelector('.disc').value) || 0;
    const gstR  = isWithoutGst ? 0 : (parseFloat(row.querySelector('.gst-rate').value) || 0);

    const lineAmt  = qty * rate;
    const discAmt  = lineAmt * disc / 100;
    const taxable  = lineAmt - discAmt;
    const gstAmt   = taxable * gstR / 100;
    const total    = taxable + gstAmt;

    row.querySelector('.taxable').value   = taxable.toFixed(2);
    row.querySelector('.gst-amt').value   = gstAmt.toFixed(2);
    row.querySelector('.total-amt').value = total.toFixed(2);
    updateSummary();
}

document.getElementById('invoice_type').addEventListener('change', function() {
    document.querySelectorAll('.item-row').forEach(row => calcRow(row));
    const isWithoutGst = this.value === 'without_gst';
    // Hide/Show GST columns based on type
    const gstCols = document.querySelectorAll('th:nth-child(7), th:nth-child(9), td:nth-child(7), td:nth-child(9)');
    gstCols.forEach(col => col.style.display = isWithoutGst ? 'none' : '');
    
    // Hide/Show Summary GST row
    const summaryGstRow = document.getElementById('summaryGst').closest('.d-flex');
    if (summaryGstRow) {
        summaryGstRow.style.display = isWithoutGst ? 'none' : 'flex';
    }
});

// Toggle Bank Details Section
const includePaymentCheckbox = document.getElementById('include_payment_info');
const bankDetailsSection = document.getElementById('bankDetailsSection');

function toggleBankDetails() {
    if (includePaymentCheckbox.checked) {
        bankDetailsSection.style.display = 'block';
    } else {
        bankDetailsSection.style.display = 'none';
    }
}

includePaymentCheckbox.addEventListener('change', toggleBankDetails);
toggleBankDetails();
updateSummary();

function numberToWordsIndian(num) {
    if (num === 0) return 'Zero';
    const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                  'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                  'Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

    function twoDigits(n) {
        if (n < 20) return ones[n];
        return tens[Math.floor(n/10)] + (n%10 ? ' ' + ones[n%10] : '');
    }
    function threeDigits(n) {
        if (n >= 100) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' and ' + twoDigits(n%100) : '');
        return twoDigits(n);
    }

    let result = '';
    if (num >= 10000000) { result += twoDigits(Math.floor(num/10000000)) + ' Crore '; num %= 10000000; }
    if (num >= 100000)   { result += twoDigits(Math.floor(num/100000)) + ' Lakh ';   num %= 100000; }
    if (num >= 1000)     { result += twoDigits(Math.floor(num/1000)) + ' Thousand '; num %= 1000; }
    if (num > 0)         { result += threeDigits(Math.floor(num)); }
    return result.trim();
}

function amountToWords(amount) {
    const rupees = Math.floor(amount);
    const paise  = Math.round((amount - rupees) * 100);
    let words = numberToWordsIndian(rupees) + ' Rupees';
    if (paise > 0) words += ' and ' + numberToWordsIndian(paise) + ' Paise';
    return words + ' Only';
}

function updateSummary() {
    let taxable = 0, gst = 0, grand = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        taxable += parseFloat(row.querySelector('.taxable').value) || 0;
        gst     += parseFloat(row.querySelector('.gst-amt').value) || 0;
        grand   += parseFloat(row.querySelector('.total-amt').value) || 0;
    });

    const rounded  = Math.round(grand);
    const roundOff = rounded - grand;

    document.getElementById('summaryTaxable').textContent = '₹' + taxable.toFixed(2);
    document.getElementById('summaryGst').textContent     = '₹' + gst.toFixed(2);
    document.getElementById('summaryRoundOff').textContent = (roundOff >= 0 ? '+' : '') + '₹' + roundOff.toFixed(2);
    document.getElementById('summaryRoundOff').style.color = roundOff >= 0 ? '#198754' : '#dc3545';
    document.getElementById('summaryTotal').textContent   = '₹' + rounded.toFixed(2);
    document.getElementById('amountInWords').textContent  = rounded > 0 ? amountToWords(rounded) : 'Zero Rupees Only';
}

function makeRow(idx) {
    const first = document.querySelector('.item-row');
    const clone = first.cloneNode(true);
    $(clone).find('select').each(function () {
        let name = $(this).attr('name');
        if (name) {
            name = name.replace(/\[\d+\]/, '[' + idx + ']');
            $(this).attr('name', name);
        }
        
        if ($(this).hasClass('select2-tags') || $(this).hasClass('select2-hidden-accessible')) {
            $(this).removeClass('select2-hidden-accessible');
            $(this).removeAttr('data-select2-id tabindex aria-hidden');
            $(this).empty().append($('#itemsBody tr:first .product-select').html());
            $(this).val('').trigger('change.select2');
            
            $(this).select2({
                theme: 'bootstrap-5',
                tags: true,
                placeholder: "Select or Type Product",
                allowClear: true
            });
        }
    });
    clone.querySelectorAll('input').forEach(el => {
        if (el.classList.contains('qty')) el.value = 1;
        else if (el.classList.contains('rate') || el.classList.contains('disc')) el.value = 0;
        else if (el.classList.contains('taxable') || el.classList.contains('gst-amt') || el.classList.contains('total-amt')) el.value = '0.00';
        else if (!el.classList.contains('gst-rate')) el.value = '';
    });
    clone.querySelector('.gst-rate').value = 0;
    return clone;
}

document.getElementById('addRow').addEventListener('click', function () {
    const tbody = document.getElementById('itemsBody');
    tbody.appendChild(makeRow(rowIndex++));
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
            updateSummary();
        }
    }
});

$(document).on('change', '.product-select', function(e) {
    const row = $(this).closest('.item-row')[0];
    
    if (this.selectedIndex > -1) {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.price !== undefined) {
            row.querySelector('.rate').value     = selectedOption.dataset.price || 0;
            row.querySelector('.uom').value      = selectedOption.dataset.unit || '';
            row.querySelector('.gst-rate').value = selectedOption.dataset.tax || 0;
            calcRow(row);
        }
    }
});

document.addEventListener('input', function (e) {
    if (e.target.closest('.item-row') &&
        (e.target.classList.contains('qty') ||
         e.target.classList.contains('rate') ||
         e.target.classList.contains('disc'))) {
        calcRow(e.target.closest('.item-row'));
    }
});
</script>
@endpush

EOF
mkdir -p resources/views/components
cat << 'EOF' > resources/views/components/topbar.blade.php
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
                    <small class="text-muted" style="font-size: 0.75rem;">Admin</small>
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

EOF
php artisan migrate --force
php artisan view:clear
echo 'Done!'

