<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\VendorPo;
use App\Models\VendorInvoice;
use Illuminate\Http\Request;

class VendorInvoiceController extends Controller
{
    public function index()
    {
        $invoices = VendorInvoice::with(['vendor', 'vendorPo'])->latest()->get();
        return view('admin.vendor_invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $vendors = Supplier::where('is_active', true)->get();
        $pos = VendorPo::where('status', 'active')->get();
        $selectedPo = null;

        if ($request->has('vendor_po_id')) {
            $selectedPo = VendorPo::with('items')->find($request->vendor_po_id);
        }

        return view('admin.vendor_invoices.create', compact('vendors', 'pos', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:suppliers,id',
            'vendor_po_id' => 'nullable|exists:vendor_pos,id',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'invoice_amount' => 'required|numeric|min:0',
            'gst_amount' => 'required|numeric|min:0',
            'tds_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoiceAmount = $validated['invoice_amount'];
        $gstAmount = $validated['gst_amount'];
        $tdsAmount = $validated['tds_amount'] ?? 0.00;
        $netPayable = $invoiceAmount + $gstAmount - $tdsAmount;

        $matchingStatus = 'matched';
        
        // 3-way matching logic
        if (!empty($validated['vendor_po_id'])) {
            $po = VendorPo::find($validated['vendor_po_id']);
            // If invoice exceeds PO value by more than 2% tolerance, flag it
            $poLimit = $po->po_value * 1.02;
            if ($netPayable > $poLimit) {
                $matchingStatus = 'flagged';
            }
        } else {
            // Direct Expense (Without PO): Auto Pass
            $matchingStatus = 'matched';
        }

        VendorInvoice::create([
            'vendor_po_id' => $validated['vendor_po_id'] ?? null,
            'vendor_id' => $validated['vendor_id'],
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'invoice_amount' => $invoiceAmount,
            'gst_amount' => $gstAmount,
            'tds_amount' => $tdsAmount,
            'net_payable' => $netPayable,
            'matching_status' => $matchingStatus,
            'approval_status' => 'pending',
            'itc_eligible' => true,
            'payment_status' => 'unpaid',
            'paid_amount' => 0.00,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.vendor-invoices.index')
            ->with('success', 'Vendor Invoice recorded successfully. Matching result: ' . strtoupper($matchingStatus));
    }

    public function show(VendorInvoice $vendor_invoice)
    {
        $vendor_invoice->load(['vendor', 'vendorPo']);
        return view('admin.vendor_invoices.show', compact('vendor_invoice'));
    }

    public function approve(VendorInvoice $vendor_invoice)
    {
        $vendor_invoice->update(['approval_status' => 'approved']);
        return redirect()->route('admin.vendor-invoices.show', $vendor_invoice)->with('success', 'Vendor Invoice approved successfully.');
    }

    public function recordPayment(Request $request, VendorInvoice $vendor_invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $vendor_invoice->balance_due,
        ]);

        $newPaid = $vendor_invoice->paid_amount + $validated['amount'];
        $status = ($newPaid >= $vendor_invoice->net_payable) ? 'paid' : 'partial';

        $vendor_invoice->update([
            'paid_amount' => $newPaid,
            'payment_status' => $status,
        ]);

        return redirect()->route('admin.vendor-invoices.show', $vendor_invoice)->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully.');
    }
}
