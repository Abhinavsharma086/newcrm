<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_no', 'invoice_id', 'courier_name', 'tracking_no',
        'vehicle_no', 'dispatch_date', 'expected_delivery', 'status',
        'delivered_at', 'notes', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'dispatch_date' => 'date',
            'expected_delivery' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
