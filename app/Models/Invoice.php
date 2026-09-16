<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_no', 'status', 'customer_id', 'biller_id', 'client_id', 'client_po_id', 'quotation_id', 'invoice_date', 'due_date',
        'subtotal', 'cgst', 'sgst', 'igst', 'total', 'payment_status',
        'paid_amount', 'notes', 'include_payment_info', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc', 'upi_id', 'upi_qr_image', 'created_by', 'branch_id',
        'billing_name', 'billing_address', 'billing_gstin', 'shipping_name', 'shipping_address', 'shipping_gstin'
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'cgst' => 'decimal:2',
            'sgst' => 'decimal:2',
            'igst' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    // Total GST (CGST + SGST + IGST)
    public function getTotalGstAttribute(): float
    {
        return round(($this->cgst ?? 0) + ($this->sgst ?? 0) + ($this->igst ?? 0), 2);
    }

    // Grand Total
    public function getTotalAmountAttribute(): float
    {
        return round(($this->subtotal ?? 0) + $this->total_gst, 2);
    }

    // Outstanding balance
    public function getBalanceDueAttribute(): float
    {
        return round(($this->total ?? 0) - ($this->paid_amount ?? 0), 2);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function biller()
    {
        return $this->belongsTo(Customer::class, 'biller_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function clientPo()
    {
        return $this->belongsTo(ClientPo::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creditDebitNotes()
    {
        return $this->hasMany(CreditDebitNote::class);
    }
}
