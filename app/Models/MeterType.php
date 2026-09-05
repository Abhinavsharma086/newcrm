<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterType extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
}
