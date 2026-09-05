<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPo extends Model
{
    protected $fillable = [
        'po_number', 'client_id', 'site_name', 'po_date',
        'po_value', 'payment_terms', 'retention_percent', 'gstin',
        'status', 'notes'
    ];

    protected $casts = [
        'po_date' => 'date',
        'po_value' => 'decimal:2',
        'retention_percent' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(ClientPoItem::class);
    }

    public function progressEntries()
    {
        return $this->hasMany(ProgressEntry::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
