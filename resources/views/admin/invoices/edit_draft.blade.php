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
                        <label class="form-label fw-semibold">Validity <span class="text-danger">*</span></label>
                        <select id="due_date_term" class="form-select @error('due_date') is-invalid @enderror" required>
                            <option value="">Select Validity</option>
                            <option value="0">Same Day</option>
                            <option value="7">7 Days</option>
                            <option value="15">15 Days</option>
                            <option value="30">30 Days</option>
                        </select>
                        <input type="hidden" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}">
                        <small class="text-muted fw-semibold" id="due_date_display"></small>
                        @error('due_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-semibold mb-0">Biller / GST Holder <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#addBillerModal">
                                <i class="fas fa-plus me-1"></i>Add New
                            </button>
                        </div>
                        <select class="form-select mt-1 @error('biller_id') is-invalid @enderror"
                                name="biller_id" id="biller_id" required>
                            <option value="">-- Select Biller --</option>
                            @foreach($billers as $biller)
                            <option value="{{ $biller->id }}" {{ old('biller_id', $invoice->biller_id) == $biller->id ? 'selected' : '' }}
                                data-gstin="{{ $biller->gstin }}"
                                data-address="{{ $biller->address }}"
                                data-state="{{ $biller->state }}"
                                data-phone="{{ $biller->phone }}">
                                {{ $biller->company_name ?: $biller->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('biller_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="billerDetails" class="mt-2 p-2 bg-light border rounded small d-none">
                            <strong>GSTIN:</strong> <span id="billerGstin"></span><br>
                            <strong>State:</strong> <span id="billerState"></span><br>
                            <strong>Address:</strong> <span id="billerAddress"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label class="form-label fw-semibold">Client (Billed To)</label>
                        <select class="form-select @error('client_id') is-invalid @enderror"
                                name="client_id" id="client_id">
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

        {{-- Bill To & Ship To Details --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-map-marker-alt text-primary me-1"></i> Bill To & Ship To Details</strong>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="sameAsBilling">
                    <label class="form-check-label small fw-bold text-muted" for="sameAsBilling">Ship To same as Bill To</label>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="row g-4">
                    {{-- Bill To --}}
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-primary mb-3">Bill To</h6>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Name</label>
                            <input type="text" class="form-control form-control-sm @error('billing_name') is-invalid @enderror" name="billing_name" id="billing_name" value="{{ old('billing_name', $invoice->billing_name) }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Address (Street, City, State, Pincode)</label>
                            <textarea class="form-control form-control-sm @error('billing_address') is-invalid @enderror" name="billing_address" id="billing_address" rows="3">{{ old('billing_address', $invoice->billing_address) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">GSTIN (if applicable)</label>
                            <input type="text" class="form-control form-control-sm @error('billing_gstin') is-invalid @enderror text-uppercase" name="billing_gstin" id="billing_gstin" value="{{ old('billing_gstin', $invoice->billing_gstin) }}" maxlength="15">
                        </div>
                    </div>
                    
                    {{-- Ship To --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3">Ship To</h6>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Name</label>
                            <input type="text" class="form-control form-control-sm @error('shipping_name') is-invalid @enderror" name="shipping_name" id="shipping_name" value="{{ old('shipping_name', $invoice->shipping_name) }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Address (Street, City, State, Pincode)</label>
                            <textarea class="form-control form-control-sm @error('shipping_address') is-invalid @enderror" name="shipping_address" id="shipping_address" rows="3">{{ old('shipping_address', $invoice->shipping_address) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">GSTIN (optional)</label>
                            <input type="text" class="form-control form-control-sm @error('shipping_gstin') is-invalid @enderror text-uppercase" name="shipping_gstin" id="shipping_gstin" value="{{ old('shipping_gstin', $invoice->shipping_gstin) }}" maxlength="15">
                        </div>
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
                                <th style="min-width:90px" class="text-center">Stock</th>
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
                                                    data-stock="{{ $p->current_stock }}"
                                                    {{ (optional($invItem->product)->name == $p->name || $invItem->description == $p->name) ? 'selected' : '' }}>
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-desc" name="items[{{ $idx }}][description]" value="{{ $invItem->description }}" placeholder="Description"></td>
                                    <td><input type="number" class="form-control form-control-sm qty text-center" name="items[{{ $idx }}][quantity]" value="{{ $invItem->quantity }}" min="0.01" step="0.01" required></td>
                                    <td><input type="text" class="form-control form-control-sm uom text-center" name="items[{{ $idx }}][unit]" value="{{ $invItem->unit }}" placeholder="Nos" readonly tabindex="-1"></td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary stock-badge">
                                            {{ optional($invItem->product)->current_stock ?? 'N/A' }}
                                        </span>
                                        <input type="hidden" class="item-stock" value="{{ optional($invItem->product)->current_stock ?? '' }}">
                                    </td>
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
                                                    data-hsn="{{ $p->hsn_code }}"
                                                    data-stock="{{ $p->current_stock }}">
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm item-desc" name="items[0][description]" placeholder="Description"></td>
                                    <td><input type="number" class="form-control form-control-sm qty text-center" name="items[0][quantity]" value="1" min="0.01" step="0.01" required></td>
                                    <td><input type="text" class="form-control form-control-sm uom text-center" name="items[0][unit]" placeholder="Nos" readonly tabindex="-1"></td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary stock-badge">N/A</span>
                                        <input type="hidden" class="item-stock" value="">
                                    </td>
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
                        {{-- Virtual QR Scanner Preview --}}
                        <div class="mt-4 p-3 bg-light border rounded text-center">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;"><i class="fas fa-qrcode text-primary me-2"></i>Virtual Scanner Preview</h6>
                            <p class="text-muted small mb-3">This QR scanner will be automatically printed on the generated invoice.</p>
                            @php
                                $upiId = 'yespay.mabs1495269ikit0072@yesbankltd';
                                $payeeName = 'METRIC QUBE ENERGY PRIVATE LIMITED';
                                $qrData = "upi://pay?pa={$upiId}&pn={$payeeName}&cu=INR";
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                            @endphp
                            <div class="d-inline-block bg-white p-2 border rounded shadow-sm">
                                <img src="{{ $qrUrl }}" alt="UPI QR" style="width: 100px; height: 100px;">
                            </div>
                            <div class="mt-2">
                                <span class="d-block fw-bold small">UPI ID</span>
                                <span class="d-block small text-muted">{{ $upiId }}</span>
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

{{-- Add Biller Modal --}}
<div class="modal fade" id="addBillerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBillerForm" action="{{ route('admin.billers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-building text-primary me-2"></i>Add New Biller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">GSTIN <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm text-uppercase" name="gstin" id="biller_gstin" maxlength="15" required>
                            <div id="billerGstSpinner" class="spinner-border spinner-border-sm text-primary position-absolute d-none" role="status" style="right: 10px; top: 6px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="company_name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">State <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="state" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Address</label>
                        <textarea class="form-control form-control-sm" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Biller</button>
                </div>
            </form>
        </div>
    </div>
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
    
    // Validate Stock
    const qtyInput = row.querySelector('.qty');
    const stockInput = row.querySelector('.item-stock');
    if (stockInput && stockInput.value !== '') {
        const stock = parseFloat(stockInput.value);
        if (qty > stock) {
            qtyInput.classList.add('is-invalid');
            qtyInput.style.borderColor = 'red';
            qtyInput.title = `Only ${stock} units available`;
        } else {
            qtyInput.classList.remove('is-invalid');
            qtyInput.style.borderColor = '';
            qtyInput.title = '';
        }
    }
    
    updateSummary();
}

// Initialize stock badges and validation on load
$('.item-row').each(function() {
    const row = this;
    const stockInput = row.querySelector('.item-stock');
    const stockBadge = row.querySelector('.stock-badge');
    
    if (stockInput && stockInput.value !== '') {
        const stock = parseFloat(stockInput.value);
        if (stock <= 0) {
            stockBadge.className = 'badge bg-danger stock-badge';
        } else if (stock <= 10) {
            stockBadge.className = 'badge bg-warning text-dark stock-badge';
        } else {
            stockBadge.className = 'badge bg-success stock-badge';
        }
        
        // Validate initial quantity
        const qtyInput = row.querySelector('.qty');
        if (qtyInput) {
            const qty = parseFloat(qtyInput.value) || 0;
            if (qty > stock) {
                qtyInput.classList.add('is-invalid');
                qtyInput.style.borderColor = 'red';
                qtyInput.title = `Only ${stock} units available`;
            }
        }
    }
});

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
    
    // Remove cloned select2 containers so they don't stack
    $(clone).find('.select2-container').remove();
    $(clone).find('[data-select2-id]').removeAttr('data-select2-id');
    
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
            
            // Clean up copied options' select2 IDs
            $(this).find('[data-select2-id]').removeAttr('data-select2-id');
            
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
        if (el.classList.contains('qty')) {
            el.value = 1;
            el.classList.remove('is-invalid');
            el.title = '';
        }
        else if (el.classList.contains('rate') || el.classList.contains('disc')) el.value = 0;
        else if (el.classList.contains('taxable') || el.classList.contains('gst-amt') || el.classList.contains('total-amt')) el.value = '0.00';
        else if (!el.classList.contains('gst-rate')) el.value = '';
    });
    clone.querySelector('.gst-rate').value = 0;
    if (clone.querySelector('.stock-badge')) {
        clone.querySelector('.stock-badge').className = 'badge bg-secondary stock-badge';
        clone.querySelector('.stock-badge').textContent = 'N/A';
    }
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
            
            const stock = selectedOption.dataset.stock;
            const stockInput = row.querySelector('.item-stock');
            const stockBadge = row.querySelector('.stock-badge');
            
            if (stock !== undefined && stock !== '') {
                stockInput.value = stock;
                stockBadge.textContent = stock;
                if (parseFloat(stock) <= 0) {
                    stockBadge.className = 'badge bg-danger stock-badge';
                } else if (parseFloat(stock) <= 10) {
                    stockBadge.className = 'badge bg-warning text-dark stock-badge';
                } else {
                    stockBadge.className = 'badge bg-success stock-badge';
                }
            } else {
                stockInput.value = '';
                stockBadge.textContent = 'N/A';
                stockBadge.className = 'badge bg-secondary stock-badge';
            }
            
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

document.getElementById('biller_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const detailsBox = document.getElementById('billerDetails');
    if(this.value) {
        document.getElementById('billerGstin').textContent = selected.dataset.gstin || 'N/A';
        document.getElementById('billerState').textContent = selected.dataset.state || 'N/A';
        document.getElementById('billerAddress').textContent = selected.dataset.address || 'N/A';
        detailsBox.classList.remove('d-none');
    } else {
        detailsBox.classList.add('d-none');
    }
});

@if(old('biller_id', $invoice->biller_id))
    document.getElementById('biller_id').dispatchEvent(new Event('change'));
@endif

// GST Auto-fetch for Add Biller Modal
const billerGstinInput = document.getElementById('biller_gstin');
if (billerGstinInput) {
    billerGstinInput.addEventListener('input', function() {
        const gstin = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        this.value = gstin;
        
        if (gstin.length === 15) {
            const spinner = document.getElementById('billerGstSpinner');
            if (spinner) spinner.classList.remove('d-none');
            
            fetch(`/admin/api/verify-gstin/${gstin}`)
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
                    if (data.success && data.data) {
                        const form = document.getElementById('addBillerForm');
                        const name = data.data.name || data.data.legal_name || data.data.trade_name;
                        
                        if (form.querySelector('input[name="company_name"]').value === '') {
                            form.querySelector('input[name="company_name"]').value = name;
                        }
                        if (form.querySelector('textarea[name="address"]').value === '') {
                            form.querySelector('textarea[name="address"]').value = data.data.address || '';
                        }
                        if (form.querySelector('input[name="state"]').value === '') {
                            form.querySelector('input[name="state"]').value = data.data.state || '';
                        }
                    }
                })
                .catch(error => {
                    if (spinner) spinner.classList.add('d-none');
                    console.error('Error fetching GST details:', error);
                });
        }
    });
}
// Due Date Term calculation
const dueDateTermSelect = document.getElementById('due_date_term');
const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
const dueDateInput = document.getElementById('due_date');
const dueDateDisplay = document.getElementById('due_date_display');

function calculateDueDate() {
    if (dueDateTermSelect.value !== '') {
        const invoiceDate = new Date(invoiceDateInput.value);
        if (!isNaN(invoiceDate.getTime())) {
            const term = parseInt(dueDateTermSelect.value);
            invoiceDate.setDate(invoiceDate.getDate() + term);
            
            const yyyy = invoiceDate.getFullYear();
            const mm = String(invoiceDate.getMonth() + 1).padStart(2, '0');
            const dd = String(invoiceDate.getDate()).padStart(2, '0');
            
            dueDateInput.value = `${yyyy}-${mm}-${dd}`;
            if(dueDateDisplay) dueDateDisplay.textContent = `Due on: ${dd}-${mm}-${yyyy}`;
        }
    } else {
        dueDateInput.value = '';
        if(dueDateDisplay) dueDateDisplay.textContent = '';
    }
}

if (dueDateTermSelect && invoiceDateInput && dueDateInput) {
    dueDateTermSelect.addEventListener('change', calculateDueDate);
    invoiceDateInput.addEventListener('change', calculateDueDate);
    
    // Auto-select on load if hidden due date exists
    if (dueDateInput.value && invoiceDateInput.value) {
        const inv = new Date(invoiceDateInput.value);
        const due = new Date(dueDateInput.value);
        if (!isNaN(inv.getTime()) && !isNaN(due.getTime())) {
            const diffTime = due - inv;
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
            
            let optionExists = false;
            for(let i=0; i<dueDateTermSelect.options.length; i++) {
                if(dueDateTermSelect.options[i].value == diffDays) {
                    optionExists = true;
                    break;
                }
            }
            if(!optionExists) {
                const opt = document.createElement('option');
                opt.value = diffDays;
                opt.text = diffDays + ' Days';
                dueDateTermSelect.add(opt);
            }
            dueDateTermSelect.value = diffDays;
            calculateDueDate();
        }
    } else {
        // Defaults to 0 or leave empty
    }
}

// Bill To & Ship To Logic
const sameAsBilling = document.getElementById('sameAsBilling');
const billingName = document.getElementById('billing_name');
const billingAddress = document.getElementById('billing_address');
const billingGstin = document.getElementById('billing_gstin');

const shippingName = document.getElementById('shipping_name');
const shippingAddress = document.getElementById('shipping_address');
const shippingGstin = document.getElementById('shipping_gstin');

function copyBillingToShipping() {
    if(sameAsBilling.checked) {
        shippingName.value = billingName.value;
        shippingAddress.value = billingAddress.value;
        shippingGstin.value = billingGstin.value;
        
        shippingName.setAttribute('readonly', true);
        shippingAddress.setAttribute('readonly', true);
        shippingGstin.setAttribute('readonly', true);
    } else {
        shippingName.removeAttribute('readonly');
        shippingAddress.removeAttribute('readonly');
        shippingGstin.removeAttribute('readonly');
    }
}

if(sameAsBilling) {
    sameAsBilling.addEventListener('change', copyBillingToShipping);
    billingName.addEventListener('input', copyBillingToShipping);
    billingAddress.addEventListener('input', copyBillingToShipping);
    billingGstin.addEventListener('input', copyBillingToShipping);
    
    // Check initially if they match
    if(billingName.value && shippingName.value === billingName.value && shippingAddress.value === billingAddress.value) {
        sameAsBilling.checked = true;
        copyBillingToShipping();
    }
}
</script>
@endpush
