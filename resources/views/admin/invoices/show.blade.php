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
                            <h5 class="mb-1 fw-bold text-dark">{{ $invoice->biller ? ($invoice->biller->company_name ?: $invoice->biller->name) : config('app.company_name', 'Metric Qube Energy Pvt. Ltd.') }}</h5>
                            <small class="text-muted">{{ $invoice->biller && $invoice->biller->address ? $invoice->biller->address . ', ' . $invoice->biller->city : config('app.company_address', 'Jaipur, Rajasthan, India') }}</small>
                            @if($invoice->biller && $invoice->biller->gstin)
                                <br><small class="text-muted">GSTIN: {{ $invoice->biller->gstin }}</small>
                            @endif
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
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="text-primary fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-1"></i> BILL TO</h6>
                                <div class="fs-5 fw-bold text-dark">{{ $invoice->billing_name ?: ($invoice->customer->company_name ?: ($invoice->customer->name ?? 'N/A')) }}</div>
                                
                                @if($invoice->billing_address)
                                    <div><i class="fas fa-map-marker-alt fa-sm me-1 text-danger"></i> {!! nl2br(e($invoice->billing_address)) !!}</div>
                                @elseif($invoice->customer)
                                    @if($invoice->customer->address)
                                        <div><i class="fas fa-map-marker-alt fa-sm me-1 text-danger"></i> {{ $invoice->customer->address }}</div>
                                    @endif
                                    @if($invoice->customer->city || $invoice->customer->state || $invoice->customer->pin)
                                        <div class="text-muted small ps-4">{{ implode(', ', array_filter([$invoice->customer->city, $invoice->customer->state, $invoice->customer->pin])) }}</div>
                                    @endif
                                @endif
                                
                                @if($invoice->billing_gstin || ($invoice->customer && $invoice->customer->gstin))
                                    <div class="mt-2">
                                        <span class="badge bg-primary fs-6 px-2 py-1"><i class="fas fa-id-card me-1"></i> GSTIN: {{ $invoice->billing_gstin ?: $invoice->customer->gstin }}</span>
                                    </div>
                                @endif
                                
                                @if(!$invoice->billing_name && $invoice->customer)
                                    @if($invoice->customer->phone)
                                        <div class="mt-2"><i class="fas fa-phone fa-sm me-1 text-success"></i> {{ $invoice->customer->phone }}</div>
                                    @endif
                                    @if($invoice->customer->email)
                                        <div><i class="fas fa-envelope fa-sm me-1 text-info"></i> {{ $invoice->customer->email }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($invoice->shipping_name)
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="text-success fw-bold mb-2"><i class="fas fa-shipping-fast me-1"></i> SHIP TO</h6>
                                <div class="fs-5 fw-bold text-dark">{{ $invoice->shipping_name }}</div>
                                @if($invoice->shipping_address)
                                    <div><i class="fas fa-map-marker-alt fa-sm me-1 text-danger"></i> {!! nl2br(e($invoice->shipping_address)) !!}</div>
                                @endif
                                @if($invoice->shipping_gstin)
                                    <div class="mt-2">
                                        <span class="badge bg-success fs-6 px-2 py-1"><i class="fas fa-id-card me-1"></i> GSTIN: {{ $invoice->shipping_gstin }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @elseif($invoice->client)
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="text-secondary fw-bold mb-2"><i class="fas fa-briefcase me-1"></i> CLIENT / BILLED TO</h6>
                                <div class="fs-5 fw-bold text-dark">{{ $invoice->client->name }}</div>
                                @if($invoice->client->address) <div><i class="fas fa-map-marker-alt fa-sm me-1 text-danger"></i> {{ $invoice->client->address }}</div>@endif
                                @if($invoice->client->phone) <div class="mt-2"><i class="fas fa-phone fa-sm me-1 text-success"></i> {{ $invoice->client->phone }}</div>@endif
                                @if($invoice->client->email) <div><i class="fas fa-envelope fa-sm me-1 text-info"></i> {{ $invoice->client->email }}</div>@endif
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

                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white py-2 text-center">
                            <strong><i class="fas fa-qrcode me-1"></i> Scan to Pay</strong>
                        </div>
                        <div class="card-body bg-light text-center">
                            @php
                                $upiId = $invoice->upi_id ?: 'yespay.mabs1495269ikit0072@yesbankltd';
                                $payeeName = $invoice->bank_account_name ?: 'METRIC QUBE ENERGY PRIVATE LIMITED';
                                $amount = $invoice->total;
                                
                                if ($upiId) {
                                    $qrData = "upi://pay?pa={$upiId}&pn={$payeeName}&am={$amount}&cu=INR";
                                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
                                } else {
                                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode("UPI ID Not Set");
                                }
                            @endphp
                            
                            <img src="{{ $qrUrl }}" alt="UPI QR" class="img-thumbnail shadow-sm p-2" style="max-height: 200px; border-radius: 10px;">
                            <div class="mt-3">
                                <h6 class="fw-bold mb-1">Scan this QR Code with any app to pay</h6>
                                <p class="text-muted mb-0">UPI ID: <strong>{{ $upiId }}</strong></p>
                            </div>
                        </div>
                    </div>
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
