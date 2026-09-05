<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BurnerType extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
}
