<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorInvoice extends Model
{
    protected $fillable = [
        'vendor_po_id', 'vendor_id', 'invoice_number', 'invoice_date',
        'invoice_amount', 'gst_amount', 'tds_amount', 'net_payable',
        'matching_status', 'approval_status', 'itc_eligible', 'payment_status',
        'paid_amount', 'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'itc_eligible' => 'boolean',
    ];

    public function vendorPo()
    {
        return $this->belongsTo(VendorPo::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function getBalanceDueAttribute()
    {
        return max(0.00, $this->net_payable - $this->paid_amount);
    }
}
