<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\GstCalculationService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with(['customer', 'invoices'])->latest()->get();
        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        $quotationNo = $this->generateQuotationNumber();
        
        return view('admin.quotations.create', compact('customers', 'products', 'quotationNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_gstin' => 'nullable|string|max:20',
            'trade_name' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'state' => 'nullable|string|max:100',
            'date' => 'required|date',
            'valid_till' => 'required|date|after_or_equal:date',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.image' => 'nullable|image|max:2048',
        ]);

        $customerName = $validated['customer_name'];
        $customerGstin = $validated['customer_gstin'] ?? null;
        $tradeName = $validated['trade_name'] ?? null;
        $billingAddress = $validated['billing_address'] ?? null;
        $city = $validated['city'] ?? null;
        $pincode = $validated['pincode'] ?? null;
        $state = $validated['state'] ?? null;

        $customer = Customer::firstOrCreate(
            ['name' => $customerName],
            [
                'phone' => '0000000000',
                'source' => 'manual',
                'gstin' => $customerGstin,
                'address' => $billingAddress,
                'city' => $city,
                'state' => $state,
                'pin' => $pincode,
            ]
        );
        
        // Update customer details if provided
        $customerUpdates = [];
        if (!empty($customerGstin) && $customer->gstin !== $customerGstin) $customerUpdates['gstin'] = $customerGstin;
        if (!empty($billingAddress) && empty($customer->address)) $customerUpdates['address'] = $billingAddress;
        if (!empty($city) && empty($customer->city)) $customerUpdates['city'] = $city;
        if (!empty($state) && empty($customer->state)) $customerUpdates['state'] = $state;
        if (!empty($pincode) && empty($customer->pin)) $customerUpdates['pin'] = $pincode;
        if (!empty($customerUpdates)) {
            $customer->update($customerUpdates);
        }
        
        $customerId = $customer->id;

        DB::transaction(function () use ($request, $validated, $customerId, $customerName, $customerGstin, $tradeName, $billingAddress, $city, $pincode, $state) {
            $quotation = Quotation::create([
                'quotation_no' => $this->generateQuotationNumber(),
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_gstin' => $customerGstin,
                'trade_name' => $tradeName,
                'billing_address' => $billingAddress,
                'city' => $city,
                'pincode' => $pincode,
                'state' => $state,
                'date' => $validated['date'],
                'valid_till' => $validated['valid_till'],
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $item) {
                $productName = $item['product_name'];
                $product = Product::where('name', $productName)->first();
                
                if ($product) {
                    $description = $product->name;
                    $taxRate = $product->tax_rate;
                    $productId = $product->id;
                } else {
                    $description = $productName;
                    $taxRate = 0; // Default tax for manual items
                    $productId = null;
                }
                
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = ($lineTotal * $taxRate) / 100;

                $imagePath = null;
                if (isset($item['image']) && $item['image']->isValid()) {
                    $file = $item['image'];
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/quotations'), $filename);
                    $imagePath = 'uploads/quotations/' . $filename;
                } elseif ($product && $product->image_path) {
                    $imagePath = $product->image_path;
                }

                $quotation->items()->create([
                    'product_id' => $productId,
                    'image_path' => $imagePath,
                    'description' => $description,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal + $lineTax,
                ]);

                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
            }

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
            ]);
        });

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation created successfully');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('customer', 'items.product', 'invoices');
        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if (in_array($quotation->status, ['accepted', 'rejected']) || $quotation->invoices()->exists()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Status for Quotation #' . $quotation->quotation_no . ' is locked and cannot be changed again.');
        }

        $customers = Customer::all();
        $products = Product::all();
        $quotation->load('items');
        
        return view('admin.quotations.edit', compact('quotation', 'customers', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if (in_array($quotation->status, ['accepted', 'rejected']) || $quotation->invoices()->exists()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Status for Quotation #' . $quotation->quotation_no . ' is locked and cannot be changed again.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected',
        ]);

        $quotation->update($validated);

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation status updated to ' . ucfirst($validated['status']) . ' successfully! Status is now locked.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted successfully');
    }

    public function downloadPdf(Quotation $quotation)
    {
        $quotation->load('customer', 'items.product');
        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation'));

        $customerName = $quotation->customer->name ?? 'Customer';
        $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', trim($customerName));
        $filename = $safeName . '-' . $quotation->quotation_no . '.pdf';

        return $pdf->download($filename);
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->invoices()->exists()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'This quotation has already been converted to an invoice.');
        }

        $gstService = app(GstCalculationService::class);
        $invoiceService = app(InvoiceService::class);
        $companyState = config('app.company_state', 'Delhi');
        $customerState = $quotation->customer->state ?? $companyState;

        return DB::transaction(function () use ($quotation, $gstService, $invoiceService, $companyState, $customerState) {
            // Generate invoice number
            $invoiceNo = $invoiceService->generateInvoiceNumber();

            $invoice = Invoice::create([
                'invoice_no'     => $invoiceNo,
                'customer_id'    => $quotation->customer_id,
                'quotation_id'   => $quotation->id,
                'invoice_date'   => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'notes'          => $quotation->notes,
                'payment_status' => 'unpaid',
                'paid_amount'    => 0,
                'created_by'     => auth()->id(),
            ]);

            $subtotal = 0; $cgstTotal = 0; $sgstTotal = 0; $igstTotal = 0;

            foreach ($quotation->items as $qItem) {
                $product   = $qItem->product;
                $taxRate   = $qItem->tax_rate ?? ($product ? $product->tax_rate : 0);
                $lineTotal = $qItem->quantity * $qItem->unit_price;
                $gst       = $gstService->calculateGst($lineTotal, $taxRate, $customerState, $companyState);

                InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'product_id'   => $qItem->product_id,
                    'description'  => $qItem->description,
                    'hsn_code'     => $product ? $product->hsn_code : null,
                    'quantity'     => $qItem->quantity,
                    'unit'         => $product ? $product->unit : null,
                    'unit_price'   => $qItem->unit_price,
                    'tax_rate'     => $taxRate,
                    'cgst'         => $gst['cgst'],
                    'sgst'         => $gst['sgst'],
                    'igst'         => $gst['igst'],
                    'total'        => $lineTotal + $gst['total_tax'],
                ]);

                $subtotal   += $lineTotal;
                $cgstTotal  += $gst['cgst'];
                $sgstTotal  += $gst['sgst'];
                $igstTotal  += $gst['igst'];
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'cgst'     => $cgstTotal,
                'sgst'     => $sgstTotal,
                'igst'     => $igstTotal,
                'total'    => $subtotal + $cgstTotal + $sgstTotal + $igstTotal,
            ]);

            // Mark quotation as accepted
            $quotation->update(['status' => 'accepted']);

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Quotation converted to Invoice #' . $invoiceNo . ' successfully!');
        });
    }

    private function generateQuotationNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "QUO-{$year}{$month}-";
        
        $lastQuotation = Quotation::where('quotation_no', 'like', $prefix . '%')
            ->orderBy('quotation_no', 'desc')
            ->first();
        
        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->quotation_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
