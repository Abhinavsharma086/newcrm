<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'product_id', 'description', 'hsn_code',
        'quantity', 'unit', 'unit_price', 'discount_percent',
        'tax_rate', 'cgst', 'sgst', 'igst', 'total'
    ];

    protected function casts(): array
    {
        return [
            'unit_price'       => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'tax_rate'         => 'decimal:2',
            'cgst'             => 'decimal:2',
            'sgst'             => 'decimal:2',
            'igst'             => 'decimal:2',
            'total'            => 'decimal:2',
        ];
    }

    // Taxable value after discount
    public function getTaxableAmountAttribute(): float
    {
        $lineTotal = $this->quantity * $this->unit_price;
        $discount  = $lineTotal * ($this->discount_percent ?? 0) / 100;
        return round($lineTotal - $discount, 2);
    }

    // Total GST on this item
    public function getGstAmountAttribute(): float
    {
        return round(($this->cgst ?? 0) + ($this->sgst ?? 0) + ($this->igst ?? 0), 2);
    }

    // Final item total including GST
    public function getTotalAmountAttribute(): float
    {
        return round($this->taxable_amount + $this->gst_amount, 2);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
