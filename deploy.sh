cd domains/erp.hisabmittra.in/public_html
mkdir -p database\migrations
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
cat << 'EOF' > resources/views/admin/invoices/pdf.blade.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #000; padding: 15px; }
        
        .main-container {
            border: 1px solid #000;
            width: 100%;
        }
        
        /* Header Section */
        .header {
            padding: 10px;
            position: relative;
        }
        .company-name {
            font-family: 'Georgia', serif;
            font-size: 24px;
            font-weight: 900;
            color: #1a1a74;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .banner {
            background-color: #009688;
            color: #fff;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 8px;
        }
        .header-info {
            width: 100%;
        }
        .header-info td {
            vertical-align: top;
            font-size: 9px;
            line-height: 1.4;
        }
        
        /* Title Bar */
        .title-bar {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
        }
        .title-bar td {
            padding: 3px 10px;
            font-weight: bold;
        }
        .title-bar .pan { width: 33%; font-size: 11px; }
        .title-bar .tax-invoice { width: 34%; text-align: center; font-size: 14px; }
        .title-bar .original { width: 33%; text-align: right; font-size: 9px; font-weight: normal; text-transform: uppercase; }

        /* Details Section */
        .details-section {
            width: 100%;
            border-collapse: collapse;
        }
        .details-section td {
            vertical-align: top;
        }
        .customer-col {
            width: 50%;
            border-right: 1px solid #000;
            padding: 0;
        }
        .invoice-col {
            width: 50%;
            padding: 0;
        }
        
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inner-table td {
            padding: 4px;
            font-size: 9px;
            border-bottom: 1px solid #eee;
        }
        .inner-table tr:last-child td { border-bottom: none; }
        .inner-table .lbl { font-weight: bold; width: 80px; }
        .section-heading {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            border-bottom: 1px solid #000;
            padding: 2px;
            background-color: #f9f9f9;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .items-table th {
            border: 1px solid #000;
            border-top: none;
            padding: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .items-table td {
            border-right: 1px solid #000;
            padding: 4px;
            font-size: 9px;
            vertical-align: top;
        }
        .items-table td:first-child { border-left: none; }
        .items-table td:last-child { border-right: none; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .items-table tbody tr td {
            border-bottom: none;
            height: 15px; 
        }
        
        .items-total-row td {
            border-top: 1px solid #000;
            border-bottom: none;
            font-weight: bold;
            padding: 4px;
        }

        /* Bottom Section */
        .bottom-section {
            width: 100%;
            border-collapse: collapse;
        }
        .bottom-left {
            width: 60%;
            border-right: 1px solid #000;
            vertical-align: top;
        }
        .bottom-right {
            width: 40%;
            vertical-align: top;
        }
        
        /* Bank Details & Totals */
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td {
            padding: 4px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }
        .summary-table td:last-child { border-right: none; }
        
        .bank-details-table { width: 100%; border-collapse: collapse; }
        .bank-details-table td { padding: 3px 4px; font-size: 9px; }
        .bank-details-table .lbl { width: 80px; }
        
        .qr-code { width: 80px; height: 80px; float: right; margin-right: 10px; margin-top: 5px; }
        
        .terms-box {
            border-top: 1px solid #000;
            padding: 4px;
            font-size: 8px;
            min-height: 50px;
        }
        .signature-box {
            border-top: 1px solid #000;
            padding: 4px;
            font-size: 9px;
            height: 40px;
        }
        
        .auth-sign-box {
            text-align: right;
            padding: 5px 10px;
            font-size: 9px;
            height: 80px;
            position: relative;
        }
        .auth-sign-text {
            position: absolute;
            bottom: 5px;
            right: 10px;
            font-weight: bold;
        }
        
        .computer-generated {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 20px;
            transform: rotate(-5deg);
        }
        
        .thank-you {
            padding: 5px 0;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        
        <!-- Header Section -->
        <div class="header">
            <table class="header-info">
                <tr>
                    <td style="width: 70%;">
                        <div class="company-name">{{ $invoice->biller ? ($invoice->biller->company_name ?: $invoice->biller->name) : config('app.company_name', 'Metric Qube Energy Pvt. Ltd.') }}</div>
                        <div class="banner">Powering Intelligent Energy Solutions</div>
                        <br>
                        @if($invoice->biller && $invoice->biller->address)
                            {!! nl2br(e($invoice->biller->address . "\n" . implode(', ', array_filter([$invoice->biller->city, $invoice->biller->state, $invoice->biller->pin])))) !!}
                        @else
                            {!! nl2br(e(config('app.company_address', "Plot No 123, Industrial Area,\nJaipur, Rajasthan - 302001"))) !!}
                        @endif
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: top;">
                        <img src="{{ public_path('MQ logo.png') }}" alt="Logo" style="max-height: 60px; max-width: 150px; margin-bottom: 5px;"><br>
                        Tel : {{ $invoice->biller ? $invoice->biller->phone : config('app.company_phone', '+91 98765 43210') }}<br>
                        Web : {{ config('app.url', 'www.metricqube.com') }}<br>
                        Email : {{ $invoice->biller ? $invoice->biller->email : config('app.company_email', 'info@metricqube.com') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Title Bar -->
        <table class="title-bar">
            <tr>
                <td class="pan">
                    @if($invoice->biller && $invoice->biller->gstin)
                        PAN : {{ substr($invoice->biller->gstin, 2, 10) }} <br>
                        GSTIN : {{ $invoice->biller->gstin }}
                    @else
                        PAN : {{ config('app.company_pan', 'ABCDE1234F') }}
                    @endif
                </td>
                <td class="tax-invoice">
                    @if($invoice->invoice_type === 'proforma') PROFORMA INVOICE
                    @elseif($invoice->invoice_type === 'without_gst') BILL OF SUPPLY
                    @else TAX INVOICE
                    @endif
                </td>
                <td class="original">ORIGINAL FOR RECIPIENT</td>
            </tr>
        </table>

        <!-- Details Section -->
        <table class="details-section">
            <tr>
                <td class="customer-col">
                    <div class="section-heading">Billed To</div>
                    <table class="inner-table">
                        <tr>
                            <td class="lbl">M/S</td>
                            <td><strong>{{ $invoice->billing_name ?: ($invoice->customer->company_name ?: $invoice->customer->name) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Address</td>
                            <td>{!! nl2br(e($invoice->billing_address ?: ($invoice->customer->address . "\n" . implode(', ', array_filter([$invoice->customer->city, $invoice->customer->state, $invoice->customer->pin]))))) !!}</td>
                        </tr>
                        <tr>
                            <td class="lbl">GSTIN</td>
                            <td><strong>{{ $invoice->billing_gstin ?: ($invoice->customer->gstin ?? 'N/A') }}</strong></td>
                        </tr>
                        @if(!$invoice->billing_name && $invoice->customer)
                        <tr>
                            <td class="lbl">Place of<br>Supply</td>
                            <td>{{ $invoice->customer->state }} ( {{ $invoice->customer->state_code ?? '08' }} )</td>
                        </tr>
                        @endif
                    </table>

                    @if($invoice->shipping_name)
                    <div class="section-heading" style="border-top: 1px solid #000;">Shipped To</div>
                    <table class="inner-table">
                        <tr>
                            <td class="lbl">M/S</td>
                            <td><strong>{{ $invoice->shipping_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Address</td>
                            <td>{!! nl2br(e($invoice->shipping_address)) !!}</td>
                        </tr>
                        <tr>
                            <td class="lbl">GSTIN</td>
                            <td><strong>{{ $invoice->shipping_gstin ?? 'N/A' }}</strong></td>
                        </tr>
                    </table>
                    @endif
                </td>
                <td class="invoice-col">
                    <table class="inner-table">
                        <tr>
                            <td class="lbl">Invoice No.</td>
                            <td><strong>{{ $invoice->invoice_no }}</strong></td>
                            <td class="lbl">Invoice Date</td>
                            <td>{{ $invoice->invoice_date->format('d-M-Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Challan No</td>
                            <td>-</td>
                            <td class="lbl">Challan Date</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td class="lbl">E-Way Bill No.</td>
                            <td colspan="3">-</td>
                        </tr>
                        <tr>
                            <td class="lbl">Transport</td>
                            <td colspan="3">-</td>
                        </tr>
                        <tr>
                            <td class="lbl">Transport ID</td>
                            <td colspan="3">-</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Determine Tax Columns -->
        @php
            $hasIgst = $invoice->igst > 0;
            $hasCgst = $invoice->cgst > 0;
            $hasSgst = $invoice->sgst > 0;
            
            if(!$hasIgst && !$hasCgst && !$hasSgst && $invoice->invoice_type !== 'without_gst') {
                $hasIgst = true; // fallback
            }
        @endphp

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 30px;">Sr.<br>No.</th>
                    <th rowspan="2">Name of Product / Service</th>
                    <th rowspan="2" style="width: 60px;">HSN / SAC</th>
                    <th rowspan="2" style="width: 40px;">Qty</th>
                    <th rowspan="2" style="width: 60px;">Rate</th>
                    <th rowspan="2" style="width: 70px;">Taxable Value</th>
                    @if($hasCgst) <th colspan="2">CGST</th> @endif
                    @if($hasSgst) <th colspan="2">SGST</th> @endif
                    @if($hasIgst) <th colspan="2">IGST</th> @endif
                    <th rowspan="2" style="width: 70px;">Total</th>
                </tr>
                <tr>
                    @if($hasCgst) <th style="width: 30px;">%</th><th style="width: 50px;">Amount</th> @endif
                    @if($hasSgst) <th style="width: 30px;">%</th><th style="width: 50px;">Amount</th> @endif
                    @if($hasIgst) <th style="width: 30px;">%</th><th style="width: 50px;">Amount</th> @endif
                </tr>
            </thead>
            <tbody>
                @php 
                    $sno = 1; 
                    $totalQty = 0;
                @endphp
                @foreach($invoice->items as $item)
                @php
                    $totalQty += $item->quantity;
                    $itemIgstRate = $item->tax_rate ?? ($item->igst > 0 ? 18 : 0);
                    $itemCgstRate = $item->tax_rate ? $item->tax_rate / 2 : ($item->cgst > 0 ? 9 : 0);
                    $itemSgstRate = $item->tax_rate ? $item->tax_rate / 2 : ($item->sgst > 0 ? 9 : 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $sno++ }}</td>
                    <td>{{ $item->description ?: ($item->product->name ?? 'N/A') }}</td>
                    <td class="text-center">{{ $item->hsn_code ?? $item->product->hsn_code ?? '-' }}</td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->unit ?? $item->product->unit ?? 'NOS' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->taxable_amount, 2) }}</td>
                    
                    @if($hasCgst)
                    <td class="text-center">{{ number_format($itemCgstRate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->cgst, 2) }}</td>
                    @endif
                    @if($hasSgst)
                    <td class="text-center">{{ number_format($itemSgstRate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->sgst, 2) }}</td>
                    @endif
                    @if($hasIgst)
                    <td class="text-center">{{ number_format($itemIgstRate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->igst, 2) }}</td>
                    @endif
                    
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
                
                <!-- Blank row for spacing if needed -->
                <tr>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                    @if($hasCgst) <td></td><td></td> @endif
                    @if($hasSgst) <td></td><td></td> @endif
                    @if($hasIgst) <td></td><td></td> @endif
                    <td></td>
                </tr>
                
                <tr class="items-total-row">
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-center">{{ $totalQty }} NOS</td>
                    <td></td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
                    
                    @if($hasCgst)
                    <td></td><td class="text-right">{{ number_format($invoice->cgst, 2) }}</td>
                    @endif
                    @if($hasSgst)
                    <td></td><td class="text-right">{{ number_format($invoice->sgst, 2) }}</td>
                    @endif
                    @if($hasIgst)
                    <td></td><td class="text-right">{{ number_format($invoice->igst, 2) }}</td>
                    @endif
                    
                    <td class="text-right">{{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Bottom Section -->
        <table class="bottom-section">
            <tr>
                <td class="bottom-left">
                    <div style="border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; font-size: 9px; background-color: #f9f9f9;">Total in words</div>
                    <div style="border-bottom: 1px solid #000; padding: 6px; text-transform: uppercase; font-weight: bold; font-size: 9px; text-align: center;">
                        {{ ucwords(numToWords($invoice->total ?? 0)) }} RUPEES ONLY
                    </div>
                    
                    <div style="border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; font-size: 9px; background-color: #f9f9f9;">Scan to Pay</div>
                    <div style="padding: 10px; text-align: center;">
                        @php
                            $upiId = $invoice->upi_id ?: 'yespay.mabs1495269ikit0072@yesbankltd';
                            $payeeName = $invoice->bank_account_name ?: 'METRIC QUBE ENERGY PRIVATE LIMITED';
                            $amount = $invoice->total;
                            $qrBase64 = null;
                            if ($upiId) {
                                $qrData = "upi://pay?pa={$upiId}&pn={$payeeName}&am={$amount}&cu=INR";
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                                try {
                                    $context = stream_context_create(['http' => ['timeout' => 3]]);
                                    $qrImage = @file_get_contents($qrUrl, false, $context);
                                    if ($qrImage) $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
                                } catch (\Exception $e) {}
                            }
                        @endphp
                        
                        @if($qrBase64)
                        <div style="text-align: center; margin: 0 auto;">
                            <img src="{{ $qrBase64 }}" alt="QR Code" style="width: 120px; height: 120px; border: 1px solid #ddd; padding: 5px;"><br>
                            <span style="font-weight: bold; font-size: 11px; display: block; margin-top: 5px;">Scan this QR Code with any app to pay</span>
                            <span style="font-size: 10px; color: #555;">UPI ID: <strong>{{ $upiId }}</strong></span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="section-heading" style="border-top: 1px solid #000;">Terms and Conditions</div>
                    <div class="terms-box">
                        Subject to Jaipur Jurisdiction.<br>
                        Our Responsibility Ceases as soon as goods leaves our Premises.<br>
                        Goods once sold will not be taken back.<br>
                        Delivery Ex-Premises.<br>
                        {!! $invoice->terms_conditions ? nl2br(e($invoice->terms_conditions)) : '' !!}
                    </div>
                    
                    <div class="signature-box">
                        Customer Signature
                    </div>
                </td>
                <td class="bottom-right">
                    <table class="summary-table">
                        <tr>
                            <td><strong>Taxable Amount</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->subtotal, 2) }}</strong></td>
                        </tr>
                        @if($hasCgst)
                        <tr>
                            <td><strong>Add : CGST</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->cgst, 2) }}</strong></td>
                        </tr>
                        @endif
                        @if($hasSgst)
                        <tr>
                            <td><strong>Add : SGST</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->sgst, 2) }}</strong></td>
                        </tr>
                        @endif
                        @if($hasIgst)
                        <tr>
                            <td><strong>Add : IGST</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->igst, 2) }}</strong></td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Total Tax</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->cgst + $invoice->sgst + $invoice->igst, 2) }}</strong></td>
                        </tr>
                        <tr style="border-bottom: 2px solid #000;">
                            <td><strong>Total Amount After Tax</strong></td>
                            <td class="text-right" style="font-size: 11px;"><strong>₹{{ number_format($invoice->total, 2) }}</strong></td>
                        </tr>
                    </table>
                    
                    <div style="padding: 5px; font-size: 8px; text-align: center; border-bottom: 1px solid #000;">
                        Certified that the particulars given above are true and correct.
                    </div>
                    <div style="padding: 5px; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                        For {{ $invoice->biller ? ($invoice->biller->company_name ?: $invoice->biller->name) : config('app.company_name', 'Metric Qube Energy Pvt. Ltd.') }}
                    </div>
                    
                    <div class="auth-sign-box">
                        <div class="computer-generated">
                            This is a computer generated<br>invoice no signature required.
                        </div>
                        <div class="auth-sign-text">Authorised Signatory</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="thank-you">
        Thank you for shopping with us!
    </div>
</body>
</html>

@php
function numToWords($num) {
    $num = (int) round($num);
    $ones = ['','one','two','three','four','five','six','seven','eight','nine',
             'ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen',
             'seventeen','eighteen','nineteen'];
    $tens = ['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
    if ($num < 20) return $ones[$num];
    if ($num < 100) return $tens[(int)($num/10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
    if ($num < 1000) return $ones[(int)($num/100)] . ' hundred' . ($num % 100 ? ' ' . numToWords($num % 100) : '');
    if ($num < 100000) return numToWords((int)($num/1000)) . ' thousand' . ($num % 1000 ? ' ' . numToWords($num % 1000) : '');
    if ($num < 10000000) return numToWords((int)($num/100000)) . ' lakh' . ($num % 100000 ? ' ' . numToWords($num % 100000) : '');
    return numToWords((int)($num/10000000)) . ' crore' . ($num % 10000000 ? ' ' . numToWords($num % 10000000) : '');
}
@endphp
EOF

