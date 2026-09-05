<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_no', 'customer_id', 'customer_name', 'customer_gstin', 'trade_name',
        'billing_address', 'city', 'pincode', 'state',
        'date', 'valid_till', 'subtotal', 'tax_amount', 'total', 'status',
        'notes', 'terms_conditions', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'valid_till' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'quotation_id');
    }
}
