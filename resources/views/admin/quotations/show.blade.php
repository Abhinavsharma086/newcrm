@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Quotation #{{ $quotation->quotation_no }}</h3>
                    <div>
                        <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-download me-1"></i> PDF
                        </a>
                        @if($quotation->invoices && $quotation->invoices->count() > 0)
                            <a href="{{ route('admin.invoices.show', $quotation->invoices->first()) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-invoice-dollar me-1"></i> View GST Bill (#{{ $quotation->invoices->first()->invoice_no }})
                            </a>
                        @else
                            <form action="{{ route('admin.quotations.convert-to-invoice', $quotation) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Convert this Quotation to GST Bill / Invoice?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-invoice me-1"></i> Convert to GST Bill</button>
                            </form>
                        @endif
                        @if(in_array($quotation->status, ['accepted', 'rejected']) || ($quotation->invoices && $quotation->invoices->count() > 0))
                            <span class="badge bg-light text-muted border py-2 px-2" title="Status has been finalized and locked">
                                <i class="fas fa-lock me-1"></i> Locked
                            </span>
                        @else
                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Change Status
                            </a>
                        @endif
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <img src="{{ asset('MQ logo.png') }}" alt="Metric Qube Logo" style="max-height: 80px; margin-bottom: 15px;">
                        <h3 class="mb-0 fw-bold" style="color: #2c3e50; font-family: 'Poppins', sans-serif;">Metric Qube Energy Pvt. Ltd.</h3>
                    </div>

                    <div class="row mb-4">
                    <div class="row mb-4">
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm rounded-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-building text-primary me-2"></i>Customer / Taxpayer Details</h6>
                                    @if($quotation->customer_gstin || $quotation->customer->gstin)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check-circle me-1"></i> GST Verified</span>
                                    @endif
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless small mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 110px;"><strong>Legal Name:</strong></td>
                                            <td class="fw-bold text-dark">{{ $quotation->customer_name ?? $quotation->customer->name }}</td>
                                        </tr>
                                        @if($quotation->trade_name)
                                        <tr>
                                            <td class="text-muted"><strong>Trade Name:</strong></td>
                                            <td class="fw-semibold text-dark">{{ $quotation->trade_name }}</td>
                                        </tr>
                                        @endif
                                        @if($quotation->customer_gstin || $quotation->customer->gstin)
                                        <tr>
                                            <td class="text-muted"><strong>GSTIN:</strong></td>
                                            <td class="fw-bold font-monospace text-primary">{{ $quotation->customer_gstin ?? $quotation->customer->gstin }}</td>
                                        </tr>
                                        @endif
                                        @if($quotation->billing_address || $quotation->customer->address)
                                        <tr>
                                            <td class="text-muted"><strong>Address:</strong></td>
                                            <td class="text-dark">{{ $quotation->billing_address ?? $quotation->customer->address }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="text-muted"><strong>Place / City:</strong></td>
                                            <td class="text-dark">{{ $quotation->city ?? ($quotation->customer->city ?? 'Jaipur') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>PinCode:</strong></td>
                                            <td class="text-dark">{{ $quotation->pincode ?? ($quotation->customer->pin ?? '302017') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>State:</strong></td>
                                            <td class="text-dark fw-semibold">{{ strtoupper($quotation->state ?? ($quotation->customer->state ?? 'RAJASTHAN')) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-end">
                            <p>
                                <strong>Date:</strong> {{ $quotation->date->format('d M Y') }}<br>
                                <strong>Valid Till:</strong> {{ $quotation->valid_till->format('d M Y') }}<br>
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $quotation->status == 'accepted' ? 'success' : ($quotation->status == 'sent' ? 'info' : ($quotation->status == 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Image</th>
                                    <th>Description</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax Rate</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($item->image_path)
                                            <img src="{{ asset($item->image_path) }}" alt="Product Image" style="max-height: 50px;">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ $item->tax_rate }}%</td>
                                    <td class="text-end">₹{{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end">Subtotal:</th>
                                    <td class="text-end">₹{{ number_format($quotation->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end">Tax:</th>
                                    <td class="text-end">₹{{ number_format($quotation->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end">Total:</th>
                                    <th class="text-end">₹{{ number_format($quotation->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($quotation->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p>{!! nl2br(e($quotation->notes)) !!}</p>
                    </div>
                    @endif

                    @if($quotation->terms_conditions)
                    <div class="mt-3">
                        <strong>Terms & Conditions:</strong>
                        <p>{!! nl2br(e($quotation->terms_conditions)) !!}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
