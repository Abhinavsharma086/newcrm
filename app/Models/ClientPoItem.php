<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPoItem extends Model
{
    protected $fillable = [
        'client_po_id', 'description', 'hsn_code', 'unit',
        'qty', 'rate', 'gst_percent', 'total_value'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function clientPo()
    {
        return $this->belongsTo(ClientPo::class);
    }

    public function progressEntries()
    {
        return $this->hasMany(ProgressEntry::class);
    }

    public function getExecutedQtyAttribute()
    {
        return $this->progressEntries()->sum('executed_qty');
    }

    public function getRemainingQtyAttribute()
    {
        return max(0, $this->qty - $this->executed_qty);
    }
}
