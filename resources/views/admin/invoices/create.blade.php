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
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-pill shadow-sm" style="font-weight: 600; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-check-circle me-2"></i> Create GST Invoice
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
