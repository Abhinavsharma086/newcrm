<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialLog extends Model
{
    protected $fillable = [
        'log_date', 'log_type', 'product_id', 'material_code', 
        'material_description', 'uom', 'qty', 'unit_rate', 
        'material_type', 'supplied_against', 
        'supplier_name', 'supplier_id', 'client_name', 'client_id', 
        'meter_no', 'wo_invoice', 'ordered_or_consumed', 
        'supporting_document', 'store_name', 'warehouse_id'
    ];

    protected $casts = [
        'log_date' => 'date',
        'qty' => 'decimal:2',
        'unit_rate' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
