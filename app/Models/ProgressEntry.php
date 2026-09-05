<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressEntry extends Model
{
    protected $fillable = [
        'client_po_id', 'client_po_item_id', 'entry_date',
        'executed_qty', 'entry_by', 'notes'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'executed_qty' => 'decimal:2',
    ];

    public function clientPo()
    {
        return $this->belongsTo(ClientPo::class);
    }

    public function item()
    {
        return $this->belongsTo(ClientPoItem::class, 'client_po_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'entry_by');
    }
}
