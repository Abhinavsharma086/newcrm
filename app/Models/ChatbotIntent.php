<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotIntent extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'intent',
        'sample_user_prompt',
    ];
}
