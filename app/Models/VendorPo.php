<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPo extends Model
{
    protected $fillable = [
        'po_number', 'vendor_id', 'site_name', 'po_date',
        'po_value', 'payment_terms', 'retention_percent', 'tds_percent', 'status',
        'irn', 'ack_no', 'ack_date', 'vendor_name', 'vendor_gstin', 'vendor_address', 'vendor_state', 'vendor_contact',
        'delivery_note', 'reference_no', 'reference_date', 'buyer_order_no', 'buyer_order_date',
        'dispatch_doc_no', 'dispatched_through', 'destination', 'terms_of_delivery',
        'consignee_name', 'consignee_address', 'consignee_gstin', 'consignee_state', 'consignee_contact_person', 'consignee_contact',
        'buyer_name', 'buyer_address', 'buyer_gstin', 'buyer_state', 'buyer_place_of_supply', 'buyer_contact_person', 'buyer_contact', 'buyer_email',
        'subtotal', 'cgst_amount', 'sgst_amount', 'igst_amount', 'round_off', 'grand_total',
        'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch', 'bill_image_path'
    ];

    protected $casts = [
        'po_date' => 'date',
        'ack_date' => 'date',
        'reference_date' => 'date',
        'buyer_order_date' => 'date',
        'po_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'retention_percent' => 'decimal:2',
        'tds_percent' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(VendorPoItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(VendorInvoice::class);
    }
}
