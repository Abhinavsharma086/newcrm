<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'query', 'intent', 'result_type',
        'result_id', 'results_count', 'response_time_ms', 'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
