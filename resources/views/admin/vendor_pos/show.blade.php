@extends('layouts.admin')

@section('title', 'Vendor Purchase Order #' . $vendor_po->po_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Vendor PO #{{ $vendor_po->po_number }}</h3>
            <p class="text-muted small mb-0">GST Tax Invoice Record & Supplier Order Details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendor-pos.pdf', $vendor_po) }}" class="btn btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            @if($vendor_po->bill_image_path)
            <a href="{{ asset($vendor_po->bill_image_path) }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-image me-1"></i> View Original Bill
            </a>
            @endif
            <a href="{{ route('admin.vendor-pos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- GST Tax Invoice Sheet (Replicating exact reference format) -->
    <div class="card border shadow-sm rounded-4 mb-5" style="border-color: #cbd5e1 !important; background: #ffffff;">
        <div class="card-body p-4 p-md-5">
            <!-- Sheet Title Header -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <span class="badge bg-light text-dark border px-3 py-1 fw-bold">GST TAX INVOICE</span>
                </div>
                <div class="text-center">
                    <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">(TRIPLICATE FOR SUPPLIER / PURCHASE ORDER)</h5>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-1">{{ strtoupper($vendor_po->status) }}</span>
                </div>
            </div>

            <!-- IRN & e-Invoice Bar (if present) -->
            @if($vendor_po->irn || $vendor_po->ack_no)
            <div class="p-3 rounded-3 mb-4 border font-monospace small" style="background: #f8fafc; font-size: 0.8rem;">
                @if($vendor_po->irn)
                <div class="text-truncate"><strong>IRN:</strong> {{ $vendor_po->irn }}</div>
                @endif
                <div class="d-flex justify-content-between flex-wrap gap-2 mt-1">
                    @if($vendor_po->ack_no)
                    <span><strong>Ack No.:</strong> {{ $vendor_po->ack_no }}</span>
                    @endif
                    @if($vendor_po->ack_date)
                    <span><strong>Ack Date:</strong> {{ $vendor_po->ack_date->format('d-M-y') }}</span>
                    @endif
                </div>
            </div>
            @endif

            <!-- 2-Column Parties and References Grid -->
            <div class="row g-0 border rounded-3 mb-4 overflow-hidden">
                <!-- Left: Supplier & Buyer Details -->
                <div class="col-md-6 border-end p-3">
                    <div class="mb-3">
                        <div class="text-muted small fw-bold text-uppercase">Supplier (Vendor)</div>
                        <h5 class="fw-bold text-dark mb-1">{{ $vendor_po->vendor_name ?? ($vendor_po->vendor->name ?? 'Supplier') }}</h5>
                        <div class="text-muted small" style="white-space: pre-line;">{{ $vendor_po->vendor_address ?? ($vendor_po->vendor->address ?? '') }}</div>
                        @if($vendor_po->vendor_gstin)
                        <div class="mt-1 small"><strong>GSTIN/UIN:</strong> <span class="font-monospace text-primary fw-bold">{{ $vendor_po->vendor_gstin }}</span></div>
                        @endif
                        @if($vendor_po->vendor_state)
                        <div class="small"><strong>State:</strong> {{ $vendor_po->vendor_state }}</div>
                        @endif
                        @if($vendor_po->vendor_contact)
                        <div class="small"><strong>Contact:</strong> {{ $vendor_po->vendor_contact }}</div>
                        @endif
                    </div>

                    <hr class="my-2">

                    <!-- Consignee (Ship To) -->
                    <div class="mb-3">
                        <div class="text-muted small fw-bold text-uppercase">Consignee (Ship to)</div>
                        <div class="fw-bold text-dark">{{ $vendor_po->consignee_name ?? 'Metric Qube Energy Pvt Ltd' }}</div>
                        <div class="text-muted small" style="white-space: pre-line;">{{ $vendor_po->consignee_address ?? 'Jaipur, Rajasthan' }}</div>
                        @if($vendor_po->consignee_gstin)
                        <div class="small"><strong>GSTIN/UIN:</strong> <span class="font-monospace">{{ $vendor_po->consignee_gstin }}</span></div>
                        @endif
                        @if($vendor_po->consignee_state)
                        <div class="small"><strong>State:</strong> {{ $vendor_po->consignee_state }}</div>
                        @endif
                        @if($vendor_po->consignee_contact_person)
                        <div class="small"><strong>Contact Person:</strong> {{ $vendor_po->consignee_contact_person }} ({{ $vendor_po->consignee_contact ?? '' }})</div>
                        @endif
                    </div>

                    <hr class="my-2">

                    <!-- Buyer (Bill To) -->
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Buyer (Bill to)</div>
                        <div class="fw-bold text-dark">{{ $vendor_po->buyer_name ?? 'Metric Qube Energy Pvt Ltd' }}</div>
                        <div class="text-muted small" style="white-space: pre-line;">{{ $vendor_po->buyer_address ?? 'Jaipur, Rajasthan' }}</div>
                        @if($vendor_po->buyer_gstin)
                        <div class="small"><strong>GSTIN/UIN:</strong> <span class="font-monospace">{{ $vendor_po->buyer_gstin }}</span></div>
                        @endif
                        @if($vendor_po->buyer_state)
                        <div class="small"><strong>State:</strong> {{ $vendor_po->buyer_state }} | <strong>Place of Supply:</strong> {{ $vendor_po->buyer_place_of_supply ?? 'Rajasthan' }}</div>
                        @endif
                    </div>
                </div>

                <!-- Right: References & Shipping Details -->
                <div class="col-md-6 p-3 bg-light bg-opacity-25">
                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" width="45%"><strong>Invoice / PO No.:</strong></td>
                            <td class="fw-bold font-monospace text-dark fs-6">{{ $vendor_po->po_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><strong>Dated:</strong></td>
                            <td class="fw-bold text-dark">{{ $vendor_po->po_date->format('d-M-Y') }}</td>
                        </tr>
                        @if($vendor_po->delivery_note)
                        <tr>
                            <td class="text-muted"><strong>Delivery Note:</strong></td>
                            <td>{{ $vendor_po->delivery_note }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->reference_no)
                        <tr>
                            <td class="text-muted"><strong>Reference No. & Date:</strong></td>
                            <td>{{ $vendor_po->reference_no }} {{ $vendor_po->reference_date ? 'dt. ' . $vendor_po->reference_date->format('d-M-y') : '' }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->buyer_order_no)
                        <tr>
                            <td class="text-muted"><strong>Buyer's Order No.:</strong></td>
                            <td>{{ $vendor_po->buyer_order_no }} {{ $vendor_po->buyer_order_date ? 'dt. ' . $vendor_po->buyer_order_date->format('d-M-y') : '' }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->dispatch_doc_no)
                        <tr>
                            <td class="text-muted"><strong>Dispatch Doc No.:</strong></td>
                            <td>{{ $vendor_po->dispatch_doc_no }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->dispatched_through)
                        <tr>
                            <td class="text-muted"><strong>Dispatched Through:</strong></td>
                            <td>{{ $vendor_po->dispatched_through }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->destination)
                        <tr>
                            <td class="text-muted"><strong>Destination:</strong></td>
                            <td>{{ $vendor_po->destination }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->terms_of_delivery)
                        <tr>
                            <td class="text-muted"><strong>Terms of Delivery:</strong></td>
                            <td>{{ $vendor_po->terms_of_delivery }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0" style="border-color: #cbd5e1;">
                    <thead style="background: #f8fafc;" class="text-center small fw-bold">
                        <tr>
                            <th width="40px">Sl<br>No</th>
                            <th style="text-align: left;">Description of Goods</th>
                            <th width="100px">HSN/SAC</th>
                            <th width="120px">Part No.</th>
                            <th width="80px">Quantity</th>
                            <th width="60px">Per</th>
                            <th width="100px">Rate (₹)</th>
                            <th width="70px">Disc %</th>
                            <th width="120px" class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor_po->items as $index => $item)
                        <tr>
                            <td class="text-center small text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->description }}</div>
                            </td>
                            <td class="text-center small font-monospace">{{ $item->hsn_code ?? '-' }}</td>
                            <td class="text-center small font-monospace">{{ $item->part_no ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ $item->qty }} {{ $item->unit ?? 'Pcs' }}</td>
                            <td class="text-center small text-muted">{{ $item->unit ?? 'Pcs' }}</td>
                            <td class="text-end">{{ number_format($item->rate, 2) }}</td>
                            <td class="text-center small">{{ $item->discount_percent > 0 ? $item->discount_percent . ' %' : '-' }}</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($item->taxable_amount ?? ($item->qty * $item->rate), 2) }}</td>
                        </tr>
                        @endforeach

                        <!-- Tax & Summary Rows -->
                        <tr style="background: #fafafa;">
                            <td colspan="7"></td>
                            <td class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold">₹{{ number_format($vendor_po->subtotal > 0 ? $vendor_po->subtotal : $vendor_po->po_value, 2) }}</td>
                        </tr>
                        @if($vendor_po->igst_amount > 0)
                        <tr>
                            <td colspan="7"></td>
                            <td class="text-end text-primary">Output IGST @ 18%:</td>
                            <td class="text-end fw-bold text-primary">₹{{ number_format($vendor_po->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->cgst_amount > 0 || $vendor_po->sgst_amount > 0)
                        <tr>
                            <td colspan="7"></td>
                            <td class="text-end text-primary">Output CGST @ 9%:</td>
                            <td class="text-end fw-bold text-primary">₹{{ number_format($vendor_po->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="7"></td>
                            <td class="text-end text-primary">Output SGST @ 9%:</td>
                            <td class="text-end fw-bold text-primary">₹{{ number_format($vendor_po->sgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($vendor_po->round_off != 0)
                        <tr>
                            <td colspan="7"></td>
                            <td class="text-end text-muted">Round Off:</td>
                            <td class="text-end text-muted">{{ ($vendor_po->round_off >= 0 ? '+' : '') . number_format($vendor_po->round_off, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background: #f0fdf4; border-top: 2px solid #16a34a;">
                            <td colspan="4" class="fw-bold text-dark">Total Quantity: {{ $vendor_po->items->sum('qty') }} Pcs</td>
                            <td colspan="3"></td>
                            <td class="text-end fw-bold fs-6 text-success">Grand Total:</td>
                            <td class="text-end fw-bold fs-5 text-success">₹{{ number_format($vendor_po->grand_total > 0 ? $vendor_po->grand_total : $vendor_po->po_value, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bank Details & Signatures -->
            <div class="row g-4 pt-3 border-top">
                <div class="col-md-7">
                    @if($vendor_po->bank_name || $vendor_po->bank_account_no)
                    <div class="p-3 rounded-3 border bg-light small">
                        <div class="fw-bold text-uppercase text-muted mb-2"><i class="fas fa-university me-1"></i> Company's Bank Details</div>
                        <div><strong>Bank Name:</strong> {{ $vendor_po->bank_name }}</div>
                        <div><strong>A/c No.:</strong> <span class="font-monospace fw-bold">{{ $vendor_po->bank_account_no }}</span></div>
                        @if($vendor_po->bank_branch || $vendor_po->bank_ifsc)
                        <div><strong>Branch & IFS Code:</strong> {{ $vendor_po->bank_branch }} & {{ $vendor_po->bank_ifsc }}</div>
                        @endif
                    </div>
                    @endif

                    <div class="text-muted small mt-3 fst-italic">
                        Declaration: We declare that this purchase order shows the actual price of the goods described and that all particulars are true and correct.
                    </div>
                </div>

                <div class="col-md-5 text-end d-flex flex-column justify-content-between">
                    <div class="small fw-bold text-uppercase text-muted">For {{ $vendor_po->vendor_name ?? 'Supplier' }}</div>
                    <div class="mt-5 pt-3 border-top d-inline-block ms-auto text-center" style="min-width: 180px;">
                        <span class="small fw-semibold text-dark">Authorized Signatory</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
