<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPoItem extends Model
{
    protected $fillable = [
        'vendor_po_id', 'description', 'hsn_code', 'part_no', 'unit',
        'qty', 'rate', 'discount_percent', 'taxable_amount', 'gst_percent',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'total_value'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function vendorPo()
    {
        return $this->belongsTo(VendorPo::class);
    }
}
