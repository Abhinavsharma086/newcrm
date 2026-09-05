@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Receive Client Purchase Order</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.client-pos.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PO Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="po_number" required placeholder="e.g. PO-2026-0091">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <select class="form-select" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PO Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="po_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Site / Locality</label>
                        <input type="text" class="form-control" name="site_name" placeholder="e.g. Site Alpha">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Retention Percentage (%)</label>
                        <input type="number" step="0.01" class="form-control" name="retention_percent" value="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client GSTIN</label>
                        <input type="text" class="form-control" name="gstin" placeholder="15-character GSTIN">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment / Contract Terms</label>
                    <textarea class="form-control" name="payment_terms" rows="2" placeholder="e.g. 30 days net, 5% retention release on completion"></textarea>
                </div>

                <h5 class="border-bottom pb-2 mb-3 mt-4">PO Service & Material Items</h5>
                <table class="table table-bordered table-sm mb-3" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>HSN/SAC</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Rate (₹)</th>
                            <th>GST %</th>
                            <th width="50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td><input type="text" class="form-control form-control-sm" name="items[0][description]" required placeholder="Description of service/goods"></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[0][hsn_code]" placeholder="HSN/SAC"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[0][qty]" required></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[0][unit]" placeholder="e.g. Job, Nos"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="items[0][rate]" required></td>
                            <td>
                                <select class="form-select form-select-sm" name="items[0][gst_percent]">
                                    <option value="18">18%</option>
                                    <option value="12">12%</option>
                                    <option value="5">5%</option>
                                    <option value="0">0%</option>
                                    <option value="28">28%</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary btn-sm mb-4" id="addRow">Add Line Item</button>

                <div class="d-flex justify-content-between border-top pt-3">
                    <a href="{{ route('admin.client-pos.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save & Activate PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;
document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('itemsBody');
    const newRow = document.querySelector('.item-row').cloneNode(true);
    newRow.querySelectorAll('input, select').forEach(el => {
        const name = el.getAttribute('name');
        if (name) {
            el.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
        }
        if (el.tagName === 'INPUT') el.value = '';
    });
    tbody.appendChild(newRow);
    rowIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
        }
    }
});
</script>
@endsection
