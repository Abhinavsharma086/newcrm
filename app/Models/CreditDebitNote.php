<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditDebitNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'note_no',
        'type',
        'invoice_id',
        'amount',
        'tax_amount',
        'reason',
        'note_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'note_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
