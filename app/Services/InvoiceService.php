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
}
