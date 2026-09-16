@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Quotation</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.quotations.store') }}" method="POST" id="quotationForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quotation_no" class="form-label">Quotation No</label>
                                    <input type="text" class="form-control" value="{{ $quotationNo }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_gstin" class="form-label fw-semibold"><i class="fas fa-id-card text-primary me-1"></i> Customer GSTIN (Auto-Fill Taxpayer)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace @error('customer_gstin') is-invalid @enderror" 
                                               id="customer_gstin" name="customer_gstin" value="{{ old('customer_gstin') }}" 
                                               placeholder="e.g. 08AATFH4878A1Z0" maxlength="15" style="text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                        <button class="btn btn-primary px-3" type="button" id="btnFetchGst" onclick="triggerGstLookup()">
                                            <i class="fas fa-bolt me-1"></i> Fetch GST Details
                                        </button>
                                    </div>
                                    <div id="gstSpinner" class="text-primary small mt-1 d-none">
                                        <div class="spinner-border spinner-border-sm me-1" role="status"></div> <span>Fetching verified taxpayer details...</span>
                                    </div>
                                    @error('customer_gstin')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label fw-semibold"><i class="fas fa-building text-primary me-1"></i> Customer / Legal Name <span class="text-danger">*</span></label>
                                    <select class="form-select select2-tags @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" required>
                                        <option value="">Select Customer or Type Below</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->name }}" 
                                                    data-gstin="{{ $customer->gstin }}"
                                                    data-address="{{ $customer->address }}"
                                                    data-city="{{ $customer->city }}"
                                                    data-state="{{ $customer->state }}"
                                                    data-pin="{{ $customer->pin }}"
                                                    {{ old('customer_name') == $customer->name ? 'selected' : '' }}>
                                                {{ $customer->name }} {{ $customer->city ? '(' . $customer->city . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Customer Taxpayer Details Section (Auto-Filled from GST / Customer) -->
                        <div class="card mb-4 border-0 shadow-sm rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                            <div class="card-header bg-transparent py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="small fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-1"></i> Taxpayer & Billing Address Details</span>
                                <span class="badge bg-light text-secondary border small" id="taxpayerBadge">Auto-Populated</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">Trade Name</label>
                                        <input type="text" class="form-control form-control-sm" id="trade_name" name="trade_name" value="{{ old('trade_name') }}" placeholder="Trade Name (if different)">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small text-muted mb-1">Registered Billing Address</label>
                                        <input type="text" class="form-control form-control-sm" id="billing_address" name="billing_address" value="{{ old('billing_address') }}" placeholder="Complete Street Address, Area, Landmark">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">Place / City</label>
                                        <input type="text" class="form-control form-control-sm" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Jaipur">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">PinCode</label>
                                        <input type="text" class="form-control form-control-sm" id="pincode" name="pincode" value="{{ old('pincode') }}" placeholder="e.g. 302017">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">State</label>
                                        <input type="text" class="form-control form-control-sm" id="state" name="state" value="{{ old('state') }}" placeholder="e.g. RAJASTHAN">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Quotation Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_till" class="form-label">Valid Till <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-select" id="validity_days" style="max-width: 130px;">
                                            <option value="">Custom</option>
                                            <option value="3">3 Days</option>
                                            <option value="7">7 Days</option>
                                            <option value="15">15 Days</option>
                                            <option value="30">30 Days</option>
                                        </select>
                                        <input type="date" class="form-control @error('valid_till') is-invalid @enderror" 
                                               id="valid_till" name="valid_till" value="{{ old('valid_till') }}" required>
                                    </div>
                                    @error('valid_till')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5>Quotation Items</h5>
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th width="100px">Quantity</th>
                                        <th width="120px">Unit Price</th>
                                        <th width="120px">Amount</th>
                                        <th width="150px">Image</th>
                                        <th width="50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <select class="form-select select2-tags product-select mb-2" name="items[0][product_name]" required>
                                                <option value="">Select Product or Type Below</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->name }}" data-price="{{ $product->price }}" data-image="{{ $product->image_path ? asset($product->image_path) : '' }}">
                                                        {{ $product->name }} {{ $product->sku ? '(' . $product->sku . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity" name="items[0][quantity]" value="1" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control unit-price" name="items[0][unit_price]" value="0" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control amount" readonly value="0.00">
                                        </td>
                                        <td>
                                            <input type="file" class="form-control form-control-sm item-image-input" name="items[0][image]" accept="image/*">
                                            <div class="mt-1 product-image-preview" style="display: none;">
                                                <span class="badge bg-success" style="font-size: 0.7em;"><i class="fas fa-check-circle"></i> Master Image</span>
                                                <img src="" class="preview-img mt-1" style="max-height: 40px; border-radius: 4px; display: block;">
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" id="addRow">Add Item</button>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="terms_conditions" class="form-label mb-0">Terms & Conditions</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="addStandardTc">
                                    <label class="form-check-label" for="addStandardTc">Include Standard T&C</label>
                                </div>
                            </div>
                            <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                      id="terms_conditions" name="terms_conditions" rows="5">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Quotation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-tags').select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: "Select or Type Below",
        allowClear: true
    });
});

let rowIndex = 1;

document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.querySelector('#itemsTable tbody');
    const newRow = document.querySelector('.item-row').cloneNode(true);
    
    // Remove select2 garbage from the cloned row
    newRow.querySelectorAll('.select2-container').forEach(el => el.remove());
    newRow.querySelectorAll('[data-select2-id]').forEach(el => el.removeAttribute('data-select2-id'));
    
    newRow.querySelectorAll('select, input').forEach(el => {
        const name = el.getAttribute('name');
        if (name) {
            el.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
        }
        if (el.classList.contains('quantity')) el.value = 1;
        if (el.classList.contains('unit-price')) el.value = 0;
        if (el.classList.contains('amount')) el.value = '0.00';
        if (el.type === 'file') el.value = '';
        if (el.tagName === 'SELECT') {
            el.classList.remove('select2-hidden-accessible');
            $(el).html($(el).html()); // reset options to clean up select2 tags if any
            el.value = '';
        }
    });
    
    tbody.appendChild(newRow);
    
    // Hide image preview in cloned row
    const previewDiv = newRow.querySelector('.product-image-preview');
    if(previewDiv) previewDiv.style.display = 'none';
    
    // Re-initialize select2 on the new row
    $(newRow).find('.select2-tags').select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: "Select or Type Below",
        allowClear: true
    });
    
    rowIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
        }
    }
});

// Auto-fill GSTIN and address when customer is selected from dropdown
$(document).on('change', '#customer_name', function(e) {
    const val = $(this).val();
    if (!val) return;
    
    // Find matching option (works for standard select and select2)
    const opt = $(this).find("option[value='" + val + "']")[0] || (this.options ? this.options[this.selectedIndex] : null);
    if (opt && opt.dataset) {
        if (opt.dataset.gstin) document.getElementById('customer_gstin').value = opt.dataset.gstin;
        if (opt.dataset.address) document.getElementById('billing_address').value = opt.dataset.address;
        if (opt.dataset.city) document.getElementById('city').value = opt.dataset.city;
        if (opt.dataset.state) document.getElementById('state').value = opt.dataset.state;
        if (opt.dataset.pin) document.getElementById('pincode').value = opt.dataset.pin;
    }
});

// Master GST Lookup function
function triggerGstLookup() {
    const input = document.getElementById('customer_gstin');
    let gstin = input.value.trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
    input.value = gstin;
    
    if (gstin.length !== 15) {
        alert('Please enter a valid 15-character GST Number.');
        return;
    }

    const spinner = document.getElementById('gstSpinner');
    if (spinner) spinner.classList.remove('d-none');
    
    fetch(`/admin/api/verify-gstin/${gstin}`)
        .then(response => response.json())
        .then(data => {
            if (spinner) spinner.classList.add('d-none');
            if (data.success && data.data) {
                const companyName = data.data.name || data.data.legal_name || data.data.trade_name;
                const select = $('#customer_name');
                
                // Select or create option in Select2
                if (select.find("option[value='" + companyName + "']").length) {
                    select.val(companyName).trigger('change.select2');
                } else { 
                    const newOption = new Option(companyName, companyName, true, true);
                    newOption.dataset.gstin = gstin;
                    newOption.dataset.address = data.data.address || '';
                    newOption.dataset.city = data.data.city || '';
                    newOption.dataset.state = data.data.state || '';
                    newOption.dataset.pin = data.data.zip || '';
                    select.append(newOption).trigger('change.select2');
                }

                // Auto-fill all Taxpayer Details
                if (data.data.trade_name) document.getElementById('trade_name').value = data.data.trade_name;
                if (data.data.address) document.getElementById('billing_address').value = data.data.address;
                if (data.data.city) document.getElementById('city').value = data.data.city;
                if (data.data.state) document.getElementById('state').value = data.data.state;
                if (data.data.zip) document.getElementById('pincode').value = data.data.zip;

                // Update badge
                const badge = document.getElementById('taxpayerBadge');
                if (badge) {
                    badge.className = 'badge bg-success text-white small';
                    badge.innerHTML = '<i class="fas fa-check-circle me-1"></i> GST Verified Taxpayer';
                }

                // Display verified feedback badge
                let feedbackEl = document.getElementById('gstFeedback');
                if (!feedbackEl) {
                    feedbackEl = document.createElement('div');
                    feedbackEl.id = 'gstFeedback';
                    feedbackEl.className = 'small fw-bold text-success mt-2 p-2 rounded';
                    feedbackEl.style.background = '#dcfce7';
                    feedbackEl.style.border = '1px solid #86efac';
                    input.closest('.mb-3').appendChild(feedbackEl);
                }
                const location = (data.data.city ? data.data.city + ', ' : '') + (data.data.state || '');
                feedbackEl.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> <strong>Verified Taxpayer:</strong> ${companyName} ${location ? '<span class="text-muted">(' + location + ')</span>' : ''}`;
            } else {
                alert('Taxpayer details could not be retrieved for this GSTIN.');
            }
        })
        .catch(error => {
            if (spinner) spinner.classList.add('d-none');
            console.error('Error fetching GST details:', error);
        });
}

// Auto-trigger on 15 chars typed or pasted in GSTIN input
document.getElementById('customer_gstin').addEventListener('input', function() {
    const cleaned = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    this.value = cleaned;
    if (cleaned.length === 15) {
        triggerGstLookup();
    }
});

document.getElementById('customer_gstin').addEventListener('paste', function() {
    setTimeout(function() {
        const input = document.getElementById('customer_gstin');
        input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (input.value.length === 15) {
            triggerGstLookup();
        }
    }, 50);
});

// Valid Till Days dropdown logic
document.getElementById('validity_days').addEventListener('change', function() {
    const days = parseInt(this.value);
    const dateInput = document.getElementById('date').value;
    
    if (days && dateInput) {
        const date = new Date(dateInput);
        date.setDate(date.getDate() + days);
        document.getElementById('valid_till').value = date.toISOString().split('T')[0];
    }
});

// If valid_till is manually changed, set dropdown to Custom
document.getElementById('valid_till').addEventListener('input', function() {
    document.getElementById('validity_days').value = '';
});

// Also update valid_till when quotation date changes if a predefined option is selected
document.getElementById('date').addEventListener('change', function() {
    const dropdown = document.getElementById('validity_days');
    if (dropdown.value) {
        dropdown.dispatchEvent(new Event('change'));
    }
});

// Update JS event listener for select2
$(document).on('change select2:select', '.product-select', function(e) {
    const row = $(this).closest('.item-row')[0];
    if(!row) return;
    let price = 0;
    let imageSrc = '';
    
    // Most robust way to get data-attributes in Select2
    const data = $(this).select2('data')[0];
    if (data && data.element) {
        const optionPrice = $(data.element).data('price');
        const optionImage = $(data.element).data('image');
        if (optionPrice !== undefined) price = optionPrice;
        if (optionImage !== undefined) imageSrc = optionImage;
    } else {
        // Fallback for non-select2 or custom tags
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.length) {
            if (selectedOption.data('price') !== undefined) price = selectedOption.data('price');
            if (selectedOption.data('image') !== undefined) imageSrc = selectedOption.data('image');
        }
    }
    
    row.querySelector('.unit-price').value = price;
    
    const previewDiv = row.querySelector('.product-image-preview');
    const previewImg = row.querySelector('.preview-img');
    if (imageSrc) {
        previewImg.src = imageSrc;
        previewDiv.style.display = 'block';
    } else {
        previewImg.src = '';
        previewDiv.style.display = 'none';
    }
    
    updateRowAmount(row);
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')) {
        updateRowAmount(e.target.closest('.item-row'));
    }
});

function updateRowAmount(row) {
    const qty = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.unit-price').value) || 0;
    const amount = qty * price;
    row.querySelector('.amount').value = amount.toFixed(2);
}

// Standard T&C Logic
const standardTc = `Terms & Conditions
1. GST: Extra @ 18%, to be mentioned separately in quotation.
4. Payment: 100% advance against Proforma Invoice.
5. Delivery: Material will be dispatched within 10 days from date of payment against Proforma Invoice.
6. Price validity: Rates are subject to change without prior intimation.
7. Dispute resolution: Subject to Jaipur jurisdiction`;

document.getElementById('addStandardTc').addEventListener('change', function() {
    const tcEl = document.getElementById('terms_conditions');
    if (this.checked) {
        if (tcEl.value.trim() === '') {
            tcEl.value = standardTc;
        } else {
            tcEl.value = tcEl.value + '\n\n' + standardTc;
        }
    } else {
        if (tcEl.value.includes(standardTc)) {
            tcEl.value = tcEl.value.replace('\n\n' + standardTc, '').replace(standardTc, '').trim();
        }
    }
});
</script>
@endpush
@endsection
