<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; }
        .page { padding: 20px; }

        /* Header */
        .header-table { width: 100%; border-bottom: 3px solid #1e40af; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; padding: 0 0 10px 0; border: none; }
        .logo-cell { width: 180px; text-align: left; }
        .logo-cell img { max-height: 55px; width: auto; }
        .company-cell { text-align: center; }
        .company-name { font-size: 18px; font-weight: bold; color: #1e40af; }
        .company-sub { font-size: 10px; color: #555; }
        .document-title-cell { text-align: right; }
        .document-title { font-size: 22px; font-weight: bold; color: #1e40af; letter-spacing: 2px; }
        .document-no { font-size: 13px; font-weight: bold; color: #333; }

        .quotation-details { margin-bottom: 20px; }
        .quotation-details table { width: 100%; border-collapse: collapse; }
        .quotation-details td { padding: 5px; border: none; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #1e40af; color: white; }
        .text-right { text-align: right; }
        .totals { margin-left: auto; width: 300px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px; border: none; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('MQ logo.png') }}" alt="Logo">
                </td>
                <td class="company-cell">
                    <div class="company-name">Metric Qube Energy Pvt. Ltd.</div>
                    <div class="company-sub">Powering Intelligent Energy Solutions</div>
                </td>
                <td class="document-title-cell">
                    <div class="document-title">QUOTATION</div>
                    <div class="document-no">#{{ $quotation->quotation_no }}</div>
                </td>
            </tr>
        </table>

    <div class="quotation-details">
        <table>
            <tr>
                <td width="55%">
                    <strong>Buyer / Customer Details:</strong><br>
                    <strong style="font-size: 13px;">{{ $quotation->customer_name ?? $quotation->customer->name }}</strong><br>
                    @if($quotation->trade_name && $quotation->trade_name !== ($quotation->customer_name ?? $quotation->customer->name))
                        <span><em>Trade Name: {{ $quotation->trade_name }}</em></span><br>
                    @endif
                    @if($quotation->billing_address ?? $quotation->customer->address)
                        <span>{{ $quotation->billing_address ?? $quotation->customer->address }}</span><br>
                    @endif
                    @if(($quotation->city ?? $quotation->customer->city) || ($quotation->pincode ?? $quotation->customer->pin))
                        <span>{{ $quotation->city ?? $quotation->customer->city }} - {{ $quotation->pincode ?? $quotation->customer->pin }}</span><br>
                    @endif
                    @if($quotation->state ?? $quotation->customer->state)
                        <span>State: {{ strtoupper($quotation->state ?? $quotation->customer->state) }}</span><br>
                    @endif
                    @if($quotation->customer_gstin ?? $quotation->customer->gstin)
                        <strong>GSTIN:</strong> {{ $quotation->customer_gstin ?? $quotation->customer->gstin }}<br>
                    @endif
                    @if($quotation->customer->phone)
                        <span>Phone: {{ $quotation->customer->phone }}</span>
                    @endif
                </td>
                <td width="50%" style="text-align: right;">
                    <strong>Date:</strong> {{ $quotation->date->format('d M Y') }}<br>
                    <strong>Valid Till:</strong> {{ $quotation->valid_till->format('d M Y') }}<br>
                    <strong>Status:</strong> {{ ucfirst($quotation->status) }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax Rate</th>
                <th class="text-right">Tax Amount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td style="text-align: center;">
                    @if($item->image_path)
                        <img src="{{ public_path($item->image_path) }}" alt="Image" style="max-height: 40px;">
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $item->tax_rate }}%</td>
                <td class="text-right">₹{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right">₹{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">₹{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Tax:</strong></td>
                <td class="text-right">₹{{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td><strong>Total:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($quotation->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if($quotation->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong><br>
        {!! nl2br(e($quotation->notes)) !!}
    </div>
    @endif

    @if($quotation->terms_conditions)
    <div style="margin-top: 20px;">
        <strong>Terms & Conditions:</strong><br>
        {!! nl2br(e($quotation->terms_conditions)) !!}
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This quotation is valid until {{ $quotation->valid_till->format('d M Y') }}</p>
    </div>
    </div>
</body>
</html>
