<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendor PO - {{ $vendor_po->po_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #111;
            line-height: 1.2;
            margin: 10px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-bordered th, .table-bordered td {
            border: 0.5px solid #444;
            padding: 3px 5px;
        }
        .header-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .box {
            border: 0.5px solid #444;
            padding: 4px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header-title">
        GST TAX INVOICE / PURCHASE ORDER (TRIPLICATE FOR SUPPLIER)
    </div>

    @if($vendor_po->irn || $vendor_po->ack_no)
    <div class="box font-mono" style="font-size: 7.5px;">
        @if($vendor_po->irn)
        <div><strong>IRN:</strong> {{ $vendor_po->irn }}</div>
        @endif
        <div>
            @if($vendor_po->ack_no) <strong>Ack No.:</strong> {{ $vendor_po->ack_no }} &nbsp;&nbsp; @endif
            @if($vendor_po->ack_date) <strong>Ack Date:</strong> {{ $vendor_po->ack_date->format('d-M-y') }} @endif
        </div>
    </div>
    @endif

    <!-- 2 Column Details Table -->
    <table class="table-bordered" style="margin-bottom: 5px;">
        <tr>
            <!-- Left: Supplier & Parties -->
            <td width="55%" valign="top">
                <div class="fw-bold" style="font-size: 10px;">{{ $vendor_po->vendor_name ?? ($vendor_po->vendor->name ?? 'Supplier') }}</div>
                <div>{!! nl2br(e($vendor_po->vendor_address ?? ($vendor_po->vendor->address ?? ''))) !!}</div>
                @if($vendor_po->vendor_gstin)
                <div><strong>GSTIN/UIN:</strong> {{ $vendor_po->vendor_gstin }}</div>
                @endif
                @if($vendor_po->vendor_state)
                <div><strong>State Name:</strong> {{ $vendor_po->vendor_state }}</div>
                @endif
                @if($vendor_po->vendor_contact)
                <div><strong>Contact:</strong> {{ $vendor_po->vendor_contact }}</div>
                @endif

                <div style="border-top: 0.5px solid #888; margin-top: 4px; padding-top: 3px;">
                    <div class="fw-bold">Consignee (Ship to):</div>
                    <div>{{ $vendor_po->consignee_name ?? 'Metric Qube Energy Pvt Ltd' }}</div>
                    <div>{{ $vendor_po->consignee_address ?? '' }}</div>
                    @if($vendor_po->consignee_gstin) <div><strong>GSTIN/UIN:</strong> {{ $vendor_po->consignee_gstin }}</div> @endif
                    @if($vendor_po->consignee_state) <div><strong>State:</strong> {{ $vendor_po->consignee_state }}</div> @endif
                </div>

                <div style="border-top: 0.5px solid #888; margin-top: 4px; padding-top: 3px;">
                    <div class="fw-bold">Buyer (Bill to):</div>
                    <div>{{ $vendor_po->buyer_name ?? 'Metric Qube Energy Pvt Ltd' }}</div>
                    <div>{{ $vendor_po->buyer_address ?? '' }}</div>
                    @if($vendor_po->buyer_gstin) <div><strong>GSTIN/UIN:</strong> {{ $vendor_po->buyer_gstin }}</div> @endif
                    @if($vendor_po->buyer_state) <div><strong>State:</strong> {{ $vendor_po->buyer_state }} | <strong>Place of Supply:</strong> {{ $vendor_po->buyer_place_of_supply ?? 'Rajasthan' }}</div> @endif
                </div>
            </td>

            <!-- Right: Order references -->
            <td width="45%" valign="top">
                <table style="border: none;">
                    <tr><td width="45%"><strong>Invoice No.:</strong></td><td class="fw-bold">{{ $vendor_po->po_number }}</td></tr>
                    <tr><td><strong>Dated:</strong></td><td>{{ $vendor_po->po_date->format('d-M-y') }}</td></tr>
                    @if($vendor_po->delivery_note)
                    <tr><td><strong>Delivery Note:</strong></td><td>{{ $vendor_po->delivery_note }}</td></tr>
                    @endif
                    @if($vendor_po->reference_no)
                    <tr><td><strong>Ref No. & Date:</strong></td><td>{{ $vendor_po->reference_no }} {{ $vendor_po->reference_date ? 'dt. ' . $vendor_po->reference_date->format('d-M-y') : '' }}</td></tr>
                    @endif
                    @if($vendor_po->buyer_order_no)
                    <tr><td><strong>Buyer Order No.:</strong></td><td>{{ $vendor_po->buyer_order_no }} {{ $vendor_po->buyer_order_date ? 'dt. ' . $vendor_po->buyer_order_date->format('d-M-y') : '' }}</td></tr>
                    @endif
                    @if($vendor_po->dispatched_through)
                    <tr><td><strong>Dispatched via:</strong></td><td>{{ $vendor_po->dispatched_through }}</td></tr>
                    @endif
                    @if($vendor_po->destination)
                    <tr><td><strong>Destination:</strong></td><td>{{ $vendor_po->destination }}</td></tr>
                    @endif
                    @if($vendor_po->terms_of_delivery)
                    <tr><td><strong>Terms:</strong></td><td>{{ $vendor_po->terms_of_delivery }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="table-bordered" style="margin-bottom: 5px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th width="20px">Sl</th>
                <th style="text-align: left;">Description of Goods</th>
                <th width="65px">HSN/SAC</th>
                <th width="75px">Part No.</th>
                <th width="45px">Qty</th>
                <th width="35px">Per</th>
                <th width="55px">Rate</th>
                <th width="40px">Disc %</th>
                <th width="65px" class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendor_po->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center font-mono">{{ $item->hsn_code ?? '-' }}</td>
                <td class="text-center font-mono">{{ $item->part_no ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $item->qty }}</td>
                <td class="text-center">{{ $item->unit ?? 'Pcs' }}</td>
                <td class="text-end">{{ number_format($item->rate, 2) }}</td>
                <td class="text-center">{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '-' }}</td>
                <td class="text-end fw-bold">{{ number_format($item->taxable_amount ?? ($item->qty * $item->rate), 2) }}</td>
            </tr>
            @endforeach

            <!-- Subtotal & Taxes -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end fw-bold">Subtotal:</td>
                <td class="text-end fw-bold">₹{{ number_format($vendor_po->subtotal > 0 ? $vendor_po->subtotal : $vendor_po->po_value, 2) }}</td>
            </tr>
            @if($vendor_po->igst_amount > 0)
            <tr>
                <td colspan="7"></td>
                <td class="text-end">Output IGST @ 18%:</td>
                <td class="text-end fw-bold">₹{{ number_format($vendor_po->igst_amount, 2) }}</td>
            </tr>
            @endif
            @if($vendor_po->cgst_amount > 0 || $vendor_po->sgst_amount > 0)
            <tr>
                <td colspan="7"></td>
                <td class="text-end">Output CGST:</td>
                <td class="text-end fw-bold">₹{{ number_format($vendor_po->cgst_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7"></td>
                <td class="text-end">Output SGST:</td>
                <td class="text-end fw-bold">₹{{ number_format($vendor_po->sgst_amount, 2) }}</td>
            </tr>
            @endif
            @if($vendor_po->round_off != 0)
            <tr>
                <td colspan="7"></td>
                <td class="text-end">Round Off:</td>
                <td class="text-end">{{ ($vendor_po->round_off >= 0 ? '+' : '') . number_format($vendor_po->round_off, 2) }}</td>
            </tr>
            @endif
            <tr style="background-color: #f2f2f2;">
                <td colspan="4" class="fw-bold">Total Qty: {{ $vendor_po->items->sum('qty') }} Pcs</td>
                <td colspan="3"></td>
                <td class="text-end fw-bold" style="font-size: 10px;">Grand Total:</td>
                <td class="text-end fw-bold" style="font-size: 10px;">₹{{ number_format($vendor_po->grand_total > 0 ? $vendor_po->grand_total : $vendor_po->po_value, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Bank Details & Signature Table -->
    <table class="table-bordered">
        <tr>
            <td width="60%" valign="top">
                @if($vendor_po->bank_name || $vendor_po->bank_account_no)
                <div class="fw-bold">Company's Bank Details:</div>
                <div><strong>Bank Name:</strong> {{ $vendor_po->bank_name }}</div>
                <div><strong>A/c No.:</strong> {{ $vendor_po->bank_account_no }}</div>
                <div><strong>Branch & IFS Code:</strong> {{ $vendor_po->bank_branch }} & {{ $vendor_po->bank_ifsc }}</div>
                @endif
                <div style="font-size: 7.5px; margin-top: 5px; color: #555;">
                    Declaration: We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
                </div>
            </td>
            <td width="40%" valign="bottom" style="text-align: right; padding-top: 30px;">
                <div style="font-size: 8px;">For {{ $vendor_po->vendor_name ?? 'Supplier' }}</div>
                <div style="margin-top: 25px; border-top: 0.5px solid #444; display: inline-block; padding-top: 2px; text-align: center; width: 120px;">
                    Authorized Signatory
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
